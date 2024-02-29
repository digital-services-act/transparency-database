<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ExceptionHandlingTrait;
use App\Http\Controllers\Traits\Sanitizer;
use App\Http\Requests\StatementStoreRequest;
use App\Models\Statement;
use App\Services\EuropeanCountriesService;
use App\Services\EuropeanLanguagesService;
use App\Services\StatementSearchService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StatementAPIController extends Controller
{
    use Sanitizer;
    use ExceptionHandlingTrait;

    protected EuropeanCountriesService $european_countries_service;
    protected StatementSearchService $statement_search_service;

    public function __construct(
        EuropeanCountriesService $european_countries_service,
        StatementSearchService $statement_search_service
    ) {
        $this->european_countries_service = $european_countries_service;
        $this->statement_search_service = $statement_search_service;
    }

    public function show(Statement $statement): Statement
    {
        return $statement;
    }

    public function existingPuid(Request $request, string $puid): JsonResponse
    {
        $platform_id = $this->getRequestUserPlatformId($request);

        $statement = Statement::query()->where('puid', $puid)->where('platform_id', $platform_id)->first();
        if ($statement) {
            return response()->json($statement, Response::HTTP_FOUND);
        }

        return response()->json(['message' => 'statement of reason not found'], Response::HTTP_NOT_FOUND);
    }

    public function store(StatementStoreRequest $request): JsonResponse
    {
        $validated = $request->safe()->merge(
            [
                'platform_id' => $this->getRequestUserPlatformId($request),
                'user_id'     => $request->user()->id,
                'method'      => Statement::METHOD_API,
            ]
        )->toArray();

        $validated = $this->sanitizeData($validated);

        try {
            $statement = Statement::create($validated);
        } catch (QueryException $queryException) {
            if (
                str_contains($queryException->getMessage(), "statements_platform_id_puid_unique") || // mysql
                str_contains($queryException->getMessage(), "UNIQUE constraint failed: statements.platform_id, statements.puid") // sqlite
            ) {
                $errors  = [
                    'puid' => [
                        'The identifier given is not unique within this platform.'
                    ]
                ];
                $message = 'The identifier given is not unique within this platform.';

                $out      = ['message' => $message, 'errors' => $errors];
                $existing = Statement::query()->where('puid', $validated['puid'])->where('platform_id', $validated['platform_id'])->first();
                if ($existing) {
                    $out['existing'] = $existing;
                }

                return response()->json($out, Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            return $this->handleQueryException($queryException, 'Statement');
        }


        $out         = $statement->toArray();
        $out['puid'] = $statement->puid; // Show the puid on a store.



        return response()->json($out, Response::HTTP_CREATED);
    }

    public function storeMultiple(Request $request): JsonResponse
    {
        if ( ! $request->user()->platform || ! $request->user()->can('create statements')) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $platform_id = $this->getRequestUserPlatformId($request);
        $user_id     = $request->user()->id;
        $method      = Statement::METHOD_API_MULTI;

        $payload = $request->validate([
            'statements' => 'required|array|between:1,100',
        ]);

        $errors = [];
        foreach ($payload['statements'] as $index => $statement) {
            $decision_visibility_other_required = in_array('DECISION_VISIBILITY_OTHER', $statement['decision_visibility'] ?? [], true);
            $content_type_other_required        = in_array('CONTENT_TYPE_OTHER', $statement['content_type'], true);

            // Create a new validator instance for each statement
            $validator = Validator::make($statement, $this->multi_rules($decision_visibility_other_required, $content_type_other_required), $this->multi_messages());

            // Check if validation fails and collect errors
            if ($validator->fails()) {
                $errors['statement_' . $index] = $validator->errors()->toArray();
            }

            try {
                $payload['statements'][$index] = $validator->validated();
            } catch (ValidationException $exception) {

            }
        }


        if ($errors !== []) {
            // Return validation errors as a JSON response
            Log::info('Statement Multiple Store Request Validation Failure', [
                'request'    => $request->all(),
                'errors'     => $errors,
                'user'       => auth()->user()->id ?? -1,
                'user_email' => auth()->user()->email ?? 'n/a',
                'platform'   => auth()->user()->platform->name ?? 'no platform'
            ]);

            return response()->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $puids_to_check = array_map(static fn($potential_statement) => $potential_statement['puid'], $payload['statements']);

        // Are all the puids unique with in the call?
        $unique_puids_to_check = array_unique($puids_to_check);
        if (count($unique_puids_to_check) !== count($puids_to_check)) {
            $errors  = [
                'puid' => [
                    'The platform identifier(s) are not all unique within this call.'
                ],
            ];
            $message = 'The platform identifier(s) are not all unique within this call.';
            $out     = ['message' => $message, 'errors' => $errors];

            return response()->json($out, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Do any of the puids already exists in the DB?
//        $existing = Statement::query()->where('platform_id', $platform_id)->whereIn('puid', $puids_to_check)->pluck('puid')->toArray();
//        if (count($existing)) {
//            $errors  = [
//                'puid'           => [
//                    'the platform identifier(s) are not all unique within this platform.'
//                ],
//                'existing_puids' => $existing
//            ];
//            $message = 'the platform identifier(s) given are not all unique within this platform.';
//            $out     = ['message' => $message, 'errors' => $errors];
//
//            return response()->json($out, Response::HTTP_UNPROCESSABLE_ENTITY);
//        }


        // enrich the payload for bulk insert.
        $now   = Carbon::now();
        $uuids = [];
        $out = [];

        foreach ($payload['statements'] as &$payload_statement) {
            $uuid                             = Str::uuid();
            $payload_statement['platform_id'] = $platform_id;
            $payload_statement['user_id']     = $user_id;
            $payload_statement['method']      = $method;
            $payload_statement['uuid']        = $uuid;
            $payload_statement['created_at']  = $now;
            $payload_statement['updated_at']  = $now;
            $out[] = $payload_statement;

            $this->sanitizePayloadStatement($payload_statement);
        }

        unset($payload_statement);

        try {
            // Bulk Insert
            Statement::insert($payload['statements']);



            return response()->json(['statements' => $out], Response::HTTP_CREATED);
        } catch (QueryException $queryException) {
            switch ($queryException->getCode()) {
                case 23000:
                    return $this->handleIntegrityConstraintException($queryException, 'Statement');
                default:
                    return $this->handleQueryException($queryException, 'Statement');
            }
        }
    }

    private function getRequestUserPlatformId(Request $request): ?int
    {
        return $request->user()->platform_id ?? null;
    }

    private function sanitizePayloadStatement(&$payload_statement): void
    {
        $this->initAllFields($payload_statement);
        $this->handleOtherFieldWithinArray($payload_statement, 'category_specification', 'KEYWORD_OTHER');
        $this->handleOtherFieldWithinArray($payload_statement, 'content_type', 'CONTENT_TYPE_OTHER');
        $this->handleOtherFieldWithinArray($payload_statement, 'decision_visibility', 'DECISION_VISIBILITY_OTHER');
        $this->handleOtherFieldWhenEqual($payload_statement, 'source_type', 'source_identity', 'SOURCE_VOLUNTARY');
        $this->handleOtherFieldWhenNotEqual($payload_statement, 'decision_monetary', 'decision_monetary_other', 'DECISION_MONETARY_OTHER');
        $this->handleOtherFieldWhenNotEqual($payload_statement, 'decision_ground', 'illegal_content_legal_ground', 'DECISION_GROUND_ILLEGAL_CONTENT');
        $this->handleOtherFieldWhenNotEqual($payload_statement, 'decision_ground', 'illegal_content_explanation', 'DECISION_GROUND_ILLEGAL_CONTENT');
        $this->handleOtherFieldWhenNotEqual($payload_statement, 'decision_ground', 'incompatible_content_ground', 'DECISION_GROUND_INCOMPATIBLE_CONTENT');
        $this->handleOtherFieldWhenNotEqual($payload_statement, 'decision_ground', 'incompatible_content_explanation', 'DECISION_GROUND_INCOMPATIBLE_CONTENT');
        $this->handleOtherFieldWhenNotEqual($payload_statement, 'decision_ground', 'incompatible_content_illegal', 'DECISION_GROUND_INCOMPATIBLE_CONTENT');

        // stringify the arrays
        foreach ($payload_statement as $key => $value) {
            if (is_array($value)) {
                $payload_statement[$key] = '["' . implode('","', $value) . '"]';
            }
        }
    }

    /**
     * @param array $payload_statement
     * @param $field
     * @param $needle
     *
     * @return void
     */
    private function handleOtherFieldWithinArray(array &$payload_statement, $field, $needle): void
    {
        $field_other = $field . '_other';
        $this->initFieldIfNotPresent($payload_statement, $field, $field_other);
        if (is_null($payload_statement[$field])) {
            return;
        }

        if (in_array($needle, $payload_statement[$field], true)) {
            $payload_statement[$field_other] ??= null;
        } else {
            $payload_statement[$field_other] = null;
        }
    }

    /**
     * @param $field
     * @param $field_other
     * @param $needle
     *
     * @return void
     */
    private function handleOtherFieldWhenEqual(array &$payload_statement, $field, $field_other, $needle): void
    {
        $this->initFieldIfNotPresent($payload_statement, $field, $field_other);
        if ($needle === $payload_statement[$field]) {
            $payload_statement[$field_other] = null;
        } else {
            $payload_statement[$field_other] ??= null;
        }
    }

    /**
     * @param $field
     * @param $field_other
     * @param $needle
     *
     * @return void
     */
    private function handleOtherFieldWhenNotEqual(array &$payload_statement, $field, $field_other, $needle): void
    {
        $this->initFieldIfNotPresent($payload_statement, $field, $field_other);
        if ($payload_statement[$field] !== $needle) {
            $payload_statement[$field_other] = null;
        } else {
            $payload_statement[$field_other] ??= null;
        }
    }

    /**
     * @param $field
     * @param $field_other
     *
     * @return void
     */
    private function initFieldIfNotPresent(array &$payload_statement, $field, $field_other): void
    {
        if ( ! isset($payload_statement[$field])) {
            $payload_statement[$field]       = null;
            $payload_statement[$field_other] = null;
        }
    }

    private function initAllFields(&$payload_statement): void
    {
        $optional_fields = [
            "decision_visibility_other",
            "decision_monetary",
            "decision_monetary_other",
            "decision_provision",
            "decision_account",
            "account_type",
            "decision_ground_reference_url",
            "content_type_other",
            "category_addition",
            "category_specification",
            "category_specification_other",
            "incompatible_content_ground",
            "incompatible_content_explanation",
            "incompatible_content_illegal",
            "content_language",
            "end_date_account_restriction",
            "end_date_monetary_restriction",
            "end_date_service_restriction",
            "end_date_visibility_restriction",
            "source_type",
            "source_identity",
            "content_date",
            "end_date_account_restriction",
            "end_date_monetary_restriction",
            "end_date_service_restriction",
            "end_date_visibility_restriction",
            "decision_ground_reference_url",
            "illegal_content_explanation",
            "incompatible_content_illegal"
        ];

        foreach ($optional_fields as $optional_field) {
            $payload_statement[$optional_field] ??= null;
        }
    }

    private function multi_rules(bool $decision_visibility_other_required, bool $content_type_other_required): array
    {
        return [
            'decision_visibility'       => ['array', $this->rule_in(array_keys(Statement::DECISION_VISIBILITIES), true), 'required_without_all:decision_monetary,decision_provision,decision_account', 'nullable'],
            'decision_visibility_other' => [
                'max:500',
                Rule::requiredIf($decision_visibility_other_required),
                Rule::excludeIf(! $decision_visibility_other_required)
            ],
            'decision_monetary'         => [$this->rule_in(array_keys(Statement::DECISION_MONETARIES), true), 'required_without_all:decision_visibility,decision_provision,decision_account', 'nullable'],
            'decision_monetary_other'   => ['required_if:decision_monetary,DECISION_MONETARY_OTHER', 'exclude_unless:decision_monetary,DECISION_MONETARY_OTHER', 'max:500'],

            'decision_provision'           => [$this->rule_in(array_keys(Statement::DECISION_PROVISIONS), true), 'required_without_all:decision_visibility,decision_monetary,decision_account', 'nullable'],
            'decision_account'             => [$this->rule_in(array_keys(Statement::DECISION_ACCOUNTS), true), 'required_without_all:decision_visibility,decision_monetary,decision_provision', 'nullable'],
            'account_type'                 => [$this->rule_in(array_keys(Statement::ACCOUNT_TYPES), true), 'nullable'],
            'category_specification'       => ['array', $this->rule_in(array_keys(Statement::KEYWORDS), true), 'nullable'],
            'category_specification_other' => ['max:500'],

            'decision_ground'                  => ['required', $this->rule_in(array_keys(Statement::DECISION_GROUNDS))],
            'decision_ground_reference_url'    => ['url', 'nullable', 'max:500'],
            'illegal_content_legal_ground'     => ['required_if:decision_ground,DECISION_GROUND_ILLEGAL_CONTENT', 'exclude_unless:decision_ground,DECISION_GROUND_ILLEGAL_CONTENT', 'max:500'],
            'illegal_content_explanation'      => ['required_if:decision_ground,DECISION_GROUND_ILLEGAL_CONTENT', 'exclude_unless:decision_ground,DECISION_GROUND_ILLEGAL_CONTENT', 'max:2000'],
            'incompatible_content_ground'      => ['required_if:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT', 'exclude_unless:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT', 'max:500'],
            'incompatible_content_explanation' => ['required_if:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT', 'exclude_unless:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT', 'max:2000'],
            'incompatible_content_illegal'     => [$this->rule_in(Statement::INCOMPATIBLE_CONTENT_ILLEGALS), 'exclude_unless:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT'],

            'content_type' => ['array', 'required', $this->rule_in(array_keys(Statement::CONTENT_TYPES))],

            'content_type_other' => [
                'max:500',
                Rule::requiredIf($content_type_other_required),
                Rule::excludeIf(! $content_type_other_required)
            ],

            'category'          => ['required', $this->rule_in(array_keys(Statement::STATEMENT_CATEGORIES))],
            'category_addition' => ['array', $this->rule_in(array_keys(Statement::STATEMENT_CATEGORIES))],

            'territorial_scope' => ['array', 'nullable', $this->rule_in(EuropeanCountriesService::EUROPEAN_COUNTRY_CODES)],

            'content_language' => [$this->rule_in(array_keys(EuropeanLanguagesService::ALL_LANGUAGES)), 'nullable'],

            'content_date'                    => ['required', 'date_format:Y-m-d', 'after_or_equal:2000-01-01', 'before_or_equal:2038-01-01'],
            'application_date'                => ['required', 'date_format:Y-m-d', 'after_or_equal:2020-01-01', 'before_or_equal:2038-01-01'],
            'end_date_account_restriction'    => ['date_format:Y-m-d', 'nullable', 'before_or_equal:2038-01-01'],
            'end_date_monetary_restriction'   => ['date_format:Y-m-d', 'nullable', 'before_or_equal:2038-01-01'],
            'end_date_service_restriction'    => ['date_format:Y-m-d', 'nullable', 'before_or_equal:2038-01-01'],
            'end_date_visibility_restriction' => ['date_format:Y-m-d', 'nullable', 'before_or_equal:2038-01-01'],

            'decision_facts'      => ['required', 'max:5000'],
            'source_type'         => ['required', $this->rule_in(array_keys(Statement::SOURCE_TYPES))],
            'source_identity'     => ['max:500', 'nullable'],
            'automated_detection' => ['required', $this->rule_in(Statement::AUTOMATED_DETECTIONS)],
            'automated_decision'  => ['required', $this->rule_in(array_keys(Statement::AUTOMATED_DECISIONS))],
            'puid'                => ['required', 'max:500'],
        ];
    }

    private function rule_in($array, $nullable = false): string
    {
        return ($nullable ? 'in:null,' : 'in:') . implode(',', $array);
    }

    private function multi_messages(): array
    {
        return [
            'decision_visibility_other.required_if'        => 'The decision visibility other field is required when decision visibility is other.',
            'decision_monetary_other.required_if'          => 'The decision monetary other field is required when decision monetary is other.',
            'content_type_other.required_if'               => 'The content type other field is required when content is other.',
            'illegal_content_legal_ground.required_if'     => 'The illegal content legal ground field is required when decision ground is illegal content.',
            'illegal_content_explanation.required_if'      => 'The illegal content explanation field is required when decision ground is illegal content.',
            'incompatible_content_ground.required_if'      => 'The incompatible content ground field is required when decision ground is incompatible content.',
            'incompatible_content_explanation.required_if' => 'The incompatible content explanation field is required when decision ground is incompatible content.',
            'content_date.date_format'                     => 'The content date does not match the format YYYY-MM-DD.',
            'application_date.date_format'                 => 'The application date does not match the format YYYY-MM-DD.',
            'end_date_account_restriction.date_format'     => 'The end date of account restriction does not match the format YYYY-MM-DD.',
            'end_date_monetary_restriction.date_format'    => 'The end date of monetary restriction does not match the format YYYY-MM-DD.',
            'end_date_service_restriction.date_format'     => 'The end date of service restriction does not match the format YYYY-MM-DD.',
            'end_date_visibility_restriction.date_format'  => 'The end date of visibility restriction does not match the format YYYY-MM-DD.',
        ];
    }
}

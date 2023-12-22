<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ExceptionHandlingTrait;
use App\Http\Controllers\Traits\Sanitizer;
use App\Http\Requests\StatementsStoreRequest;
use App\Http\Requests\StatementStoreRequest;
use App\Models\Statement;
use App\Services\EuropeanCountriesService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StatementAPIController extends Controller
{
    use Sanitizer, ExceptionHandlingTrait;

    protected EuropeanCountriesService $european_countries_service;

    public function __construct(
        EuropeanCountriesService $european_countries_service,
    ) {
        $this->european_countries_service = $european_countries_service;
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
        } catch (QueryException $e) {
            if (
                str_contains($e->getMessage(), "statements_platform_id_puid_unique") || // mysql
                str_contains($e->getMessage(), "UNIQUE constraint failed: statements.platform_id, statements.puid") // sqlite
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

            return $this->handleQueryException($e, 'Statement');
        }


        $out         = $statement->toArray();
        $out['puid'] = $statement->puid; // Show the puid on a store.

        return response()->json($out, Response::HTTP_CREATED);
    }

    public function storeMultiple(Request $request): JsonResponse
    {
        $platform_id = $this->getRequestUserPlatformId($request);
        $user_id     = $request->user()->id;
        $method      = Statement::METHOD_API_MULTI;


        $payload = $request->validate([
            'statements' => 'required|array',
        ]);

        $statementValidator = new StatementsStoreRequest();

        $errors = [];
        foreach ($payload['statements'] as $index => $statement) {
            // Create a new validator instance for each statement
            $validator = Validator::make($statement, $statementValidator->rules($index));

            // Check if validation fails and collect errors
            if ($validator->fails()) {
                $errors["statement_{$index}"] = $validator->errors()->toArray();
            }
        }


        if ( ! empty($errors)) {
            // Return validation errors as a JSON response
            return response()->json(['errors' => $errors], 422);
        }

        $puids_to_check = array_map(static function ($potential_statement) {
            return $potential_statement['puid'];
        }, $payload['statements']);

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
        $existing = Statement::query()->where('platform_id', $platform_id)->whereIn('puid', $puids_to_check)->pluck('puid')->toArray();
        if (count($existing)) {
            $errors  = [
                'puid'           => [
                    'the platform identifier(s) are not all unique within this platform.'
                ],
                'existing_puids' => $existing
            ];
            $message = 'the platform identifier(s) given are not all unique within this platform.';
            $out     = ['message' => $message, 'errors' => $errors];

            return response()->json($out, Response::HTTP_UNPROCESSABLE_ENTITY);
        }


        // enrich the payload for bulk insert.
        $now   = Carbon::now();
        $uuids = [];
        foreach ($payload['statements'] as $index => $potential_statement) {
            $uuid                                         = Str::uuid();
            $uuids[]                                      = $uuid;
            $payload['statements'][$index]['platform_id'] = $platform_id;
            $payload['statements'][$index]['user_id']     = $user_id;
            $payload['statements'][$index]['method']      = $method;
            $payload['statements'][$index]['uuid']        = $uuid;
            $payload['statements'][$index]['created_at']  = $now;
            $payload['statements'][$index]['updated_at']  = $now;

            $this->initAllFields($payload['statements'][$index]);
            $payload = $this->validatePayloadStatements($payload, $index);

            // stringify the arrays
            foreach ($payload['statements'][$index] as $key => $value) {
                if (is_array($value)) {
                    $payload['statements'][$index][$key] = '["' . implode('","', $value) . '"]';
                }
            }
        }

        try {
            // Bulk Insert
            Statement::insert($payload['statements']);

            // Get them back
            $created_statements = Statement::query()->whereIn('uuid', $uuids)->get();

            // Build an output.
            $out = [];
            foreach ($created_statements as $created_statement) {
                $puid                      = $created_statement->puid;
                $created_statement         = $created_statement->toArray();
                $created_statement['puid'] = $puid;
                $out[]                     = $created_statement;
            }

            return response()->json(['statements' => $out], Response::HTTP_CREATED);
        } catch (QueryException $e) {
            return $this->handleQueryException($e, 'Statement');
        }
    }

    private function getRequestUserPlatformId(Request $request): ?int
    {
        return $request->user()->platform_id ?? null;
    }

    /**
     * @param array $payload
     * @param int|string $index
     *
     * @return void
     */
    public function handleOtherFieldWithinArray(array &$payload, int|string $index, $field, $needle)
    {
        $field_other = $field . '_other';
        $payload     = $this->initFieldIfNotPresent($payload, $index, $field, $field_other);
        if (is_null($payload['statements'][$index][$field])) {
            return;
        }
        if (in_array($needle, $payload['statements'][$index][$field])) {
            $payload['statements'][$index][$field_other] = $payload['statements'][$index][$field_other] ?? null;
        } else {
            $payload['statements'][$index][$field_other] = null;
        }
    }

    public function handleOtherFieldWhenEqual(array &$payload, int|string $index, $field, $field_other, $needle)
    {
        $payload = $this->initFieldIfNotPresent($payload, $index, $field, $field_other);
        if ($payload['statements'][$index][$field] == $needle) {
            $payload['statements'][$index][$field_other] = null;
        } else {
            $payload['statements'][$index][$field_other] = $payload['statements'][$index][$field_other] ?? null;
        }
    }

    public function handleOtherFieldWhenNotEqual(array &$payload, int|string $index, $field, $field_other, $needle)
    {
        $payload = $this->initFieldIfNotPresent($payload, $index, $field, $field_other);
        if ($payload['statements'][$index][$field] !== $needle) {
            $payload['statements'][$index][$field_other] = null;
        } else {
            $payload['statements'][$index][$field_other] = $payload['statements'][$index][$field_other] ?? null;
        }
    }

    /**
     * @param array $payload
     * @param int|string $index
     *
     * @return array
     */
    public function validatePayloadStatements(array $payload, int|string $index): array
    {
        $this->handleOtherFieldWithinArray($payload, $index, 'category_specification', 'KEYWORD_OTHER');
        $this->handleOtherFieldWithinArray($payload, $index, 'content_type', 'CONTENT_TYPE_OTHER');
        $this->handleOtherFieldWithinArray($payload, $index, 'decision_visibility', 'DECISION_VISIBILITY_OTHER');

        $this->handleOtherFieldWhenEqual($payload, $index, 'source_type', 'source_identity', 'SOURCE_VOLUNTARY');
        $this->handleOtherFieldWhenNotEqual($payload, $index, 'decision_monetary', 'decision_monetary_other', 'DECISION_MONETARY_OTHER');
        $this->handleOtherFieldWhenNotEqual($payload, $index, 'decision_ground', 'illegal_content_legal_ground', 'DECISION_GROUND_ILLEGAL_CONTENT');
        $this->handleOtherFieldWhenNotEqual($payload, $index, 'decision_ground', 'illegal_content_explanation', 'DECISION_GROUND_ILLEGAL_CONTENT');
        $this->handleOtherFieldWhenNotEqual($payload, $index, 'decision_ground', 'incompatible_content_ground', 'DECISION_GROUND_INCOMPATIBLE_CONTENT');
        $this->handleOtherFieldWhenNotEqual($payload, $index, 'decision_ground', 'incompatible_content_explanation', 'DECISION_GROUND_INCOMPATIBLE_CONTENT');
        $this->handleOtherFieldWhenNotEqual($payload, $index, 'decision_ground', 'incompatible_content_illegal', 'DECISION_GROUND_INCOMPATIBLE_CONTENT');

        return $payload;
    }

    /**
     * @param array $payload
     * @param int|string $index
     * @param $field
     * @param $field_other
     *
     * @return array
     */
    public function initFieldIfNotPresent(array $payload, int|string $index, $field, $field_other): array
    {
        if ( ! isset($payload['statements'][$index][$field])) {
            $payload['statements'][$index][$field]       = null;
            $payload['statements'][$index][$field_other] = null;
        }

        return $payload;
    }

    private function initAllFields(&$statement)
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
            $statement[$optional_field] = $statement[$optional_field] ?? null;
        }
    }
}

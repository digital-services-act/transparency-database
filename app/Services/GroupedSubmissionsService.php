<?php

namespace App\Services;

use App\Http\Controllers\Api\v1\StatementMultipleAPIController;
use App\Models\Statement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class GroupedSubmissionsService
{

    public function sanitizePayloadStatement(&$payload_statement): void
    {
        $this->initAllFields($payload_statement);
        $this->handleOtherFieldWithinArray($payload_statement, 'category_specification', 'KEYWORD_OTHER');
        $this->handleOtherFieldWithinArray($payload_statement, 'content_type', 'CONTENT_TYPE_OTHER');
        $this->handleOtherFieldWithinArray($payload_statement, 'decision_visibility', 'DECISION_VISIBILITY_OTHER');
        $this->handleOtherFieldWhenEqual($payload_statement, 'source_type', 'source_identity', 'SOURCE_VOLUNTARY');
        $this->handleOtherFieldWhenNotEqual($payload_statement, 'decision_monetary', 'decision_monetary_other',
            'DECISION_MONETARY_OTHER');
        $this->handleOtherFieldWhenNotEqual($payload_statement, 'decision_ground', 'illegal_content_legal_ground',
            'DECISION_GROUND_ILLEGAL_CONTENT');
        $this->handleOtherFieldWhenNotEqual($payload_statement, 'decision_ground', 'illegal_content_explanation',
            'DECISION_GROUND_ILLEGAL_CONTENT');
        $this->handleOtherFieldWhenNotEqual($payload_statement, 'decision_ground', 'incompatible_content_ground',
            'DECISION_GROUND_INCOMPATIBLE_CONTENT');
        $this->handleOtherFieldWhenNotEqual($payload_statement, 'decision_ground', 'incompatible_content_explanation',
            'DECISION_GROUND_INCOMPATIBLE_CONTENT');
        $this->handleOtherFieldWhenNotEqual($payload_statement, 'decision_ground', 'incompatible_content_illegal',
            'DECISION_GROUND_INCOMPATIBLE_CONTENT');

        // stringify the arrays
        foreach ($payload_statement as $key => $value) {
            if (is_array($value)) {
                if (!empty($value)){
                    $payload_statement[$key] = '["' . implode('","', $value) . '"]';
                } else {
                    $payload_statement[$key] = '[]';
                }

            }
        }
    }

    /**
     * @param $field
     * @param $needle
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
        if (!isset($payload_statement[$field])) {
            $payload_statement[$field] = null;
            $payload_statement[$field_other] = null;
        }
    }

    /**
     * @return array
     */
    public function sanitizePayload(
        array $payload,
        array $errors,
    ): array {
        foreach ($payload['statements'] as $index => $statement) {
            $decision_visibility_other_required = in_array('DECISION_VISIBILITY_OTHER',
                $statement['decision_visibility'] ?? [], true);
            $content_type_other_required = in_array('CONTENT_TYPE_OTHER', $statement['content_type'] ?? [], true);

            // Create a new validator instance for each statement
            $validator = Validator::make($statement,
                $this->multi_rules($decision_visibility_other_required, $content_type_other_required),
                $this->multi_messages());

            // Check if validation fails and collect errors
            if ($validator->fails()) {
                $errors['statement_' . $index] = $validator->errors()->toArray();
            }

            try {
                $payload['statements'][$index] = $validator->validated();
            } catch (ValidationException) {
            }
        }

        return [$errors, $payload];
    }

    private function initArrayFields(&$statement): void
    {
        $array_fields = [
            "decision_visibility",
            "category_addition",
            "category_specification",
            "content_type",
            "territorial_scope"
        ];

        foreach ($array_fields as $array_field) {
            $statement[$array_field] ??= [];
        }
    }

    private function removeHiddenFields(&$statement): void
    {
        $hidden_fields = [
            'id',
            'deleted_at',
            'updated_at',
            'method',
            'user_id',
            'platform',
            'platform_id'
        ];

        foreach ($hidden_fields as $hidden) {
            unset($statement[$hidden]);
        }
    }


    private function initOptionalFields(&$statement): void
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
            "incompatible_content_illegal",
            "illegal_content_legal_ground"
        ];


        foreach ($optional_fields as $optional_field) {
            $statement[$optional_field] ??= null;
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
            'decision_visibility' => [
                'array',
                $this->rule_in(array_keys(Statement::DECISION_VISIBILITIES), true),
                'required_without_all:decision_monetary,decision_provision,decision_account',
                'nullable'
            ],
            'decision_visibility_other' => [
                'max:500',
                Rule::requiredIf($decision_visibility_other_required),
                Rule::excludeIf(!$decision_visibility_other_required)
            ],
            'decision_monetary' => [
                $this->rule_in(array_keys(Statement::DECISION_MONETARIES), true),
                'required_without_all:decision_visibility,decision_provision,decision_account',
                'nullable'
            ],
            'decision_monetary_other' => [
                'required_if:decision_monetary,DECISION_MONETARY_OTHER',
                'exclude_unless:decision_monetary,DECISION_MONETARY_OTHER',
                'max:500'
            ],

            'decision_provision' => [
                $this->rule_in(array_keys(Statement::DECISION_PROVISIONS), true),
                'required_without_all:decision_visibility,decision_monetary,decision_account',
                'nullable'
            ],
            'decision_account' => [
                $this->rule_in(array_keys(Statement::DECISION_ACCOUNTS), true),
                'required_without_all:decision_visibility,decision_monetary,decision_provision',
                'nullable'
            ],
            'account_type' => [$this->rule_in(array_keys(Statement::ACCOUNT_TYPES), true), 'nullable'],
            'category_specification' => ['array', $this->rule_in(array_keys(Statement::KEYWORDS), true), 'nullable'],
            'category_specification_other' => ['max:500'],

            'decision_ground' => ['required', $this->rule_in(array_keys(Statement::DECISION_GROUNDS))],
            'decision_ground_reference_url' => ['url', 'nullable', 'max:500'],
            'illegal_content_legal_ground' => [
                'required_if:decision_ground,DECISION_GROUND_ILLEGAL_CONTENT',
                'exclude_unless:decision_ground,DECISION_GROUND_ILLEGAL_CONTENT',
                'max:500'
            ],
            'illegal_content_explanation' => [
                'required_if:decision_ground,DECISION_GROUND_ILLEGAL_CONTENT',
                'exclude_unless:decision_ground,DECISION_GROUND_ILLEGAL_CONTENT',
                'max:2000'
            ],
            'incompatible_content_ground' => [
                'required_if:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT',
                'exclude_unless:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT',
                'max:500'
            ],
            'incompatible_content_explanation' => [
                'required_if:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT',
                'exclude_unless:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT',
                'max:2000'
            ],
            'incompatible_content_illegal' => [
                $this->rule_in(Statement::INCOMPATIBLE_CONTENT_ILLEGALS),
                'exclude_unless:decision_ground,DECISION_GROUND_INCOMPATIBLE_CONTENT'
            ],

            'content_type' => ['array', 'required', $this->rule_in(array_keys(Statement::CONTENT_TYPES))],

            'content_type_other' => [
                'max:500',
                Rule::requiredIf($content_type_other_required),
                Rule::excludeIf(!$content_type_other_required)
            ],

            'category' => ['required', $this->rule_in(array_keys(Statement::STATEMENT_CATEGORIES))],
            'category_addition' => ['array', $this->rule_in(array_keys(Statement::STATEMENT_CATEGORIES))],

            'territorial_scope' => [
                'array',
                'nullable',
                $this->rule_in(EuropeanCountriesService::EUROPEAN_COUNTRY_CODES)
            ],

            'content_language' => [$this->rule_in(array_keys(EuropeanLanguagesService::ALL_LANGUAGES)), 'nullable'],

            'content_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:2000-01-01',
                'before_or_equal:2038-01-01'
            ],
            'application_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:2020-01-01',
                'before_or_equal:2038-01-01'
            ],
            'end_date_account_restriction' => ['date_format:Y-m-d', 'nullable', 'before_or_equal:2038-01-01'],
            'end_date_monetary_restriction' => ['date_format:Y-m-d', 'nullable', 'before_or_equal:2038-01-01'],
            'end_date_service_restriction' => ['date_format:Y-m-d', 'nullable', 'before_or_equal:2038-01-01'],
            'end_date_visibility_restriction' => ['date_format:Y-m-d', 'nullable', 'before_or_equal:2038-01-01'],

            'decision_facts' => ['required', 'max:5000'],
            'source_type' => ['required', $this->rule_in(array_keys(Statement::SOURCE_TYPES))],
            'source_identity' => ['max:500', 'nullable'],
            'automated_detection' => ['required', $this->rule_in(Statement::AUTOMATED_DETECTIONS)],
            'automated_decision' => ['required', $this->rule_in(array_keys(Statement::AUTOMATED_DECISIONS))],
            'puid' => ['required', 'max:500', 'regex:/^[a-zA-Z0-9-_]+$/D'],
        ];
    }

    private function rule_in($array, $nullable = false): string
    {
        return ($nullable ? 'in:null,' : 'in:') . implode(',', $array);
    }

    private function multi_messages(): array
    {
        return [
            'decision_visibility_other.required_if' => 'The decision visibility other field is required when decision visibility is other.',
            'decision_monetary_other.required_if' => 'The decision monetary other field is required when decision monetary is other.',
            'content_type_other.required_if' => 'The content type other field is required when content is other.',
            'illegal_content_legal_ground.required_if' => 'The illegal content legal ground field is required when decision ground is illegal content.',
            'illegal_content_explanation.required_if' => 'The illegal content explanation field is required when decision ground is illegal content.',
            'incompatible_content_ground.required_if' => 'The incompatible content ground field is required when decision ground is incompatible content.',
            'incompatible_content_explanation.required_if' => 'The incompatible content explanation field is required when decision ground is incompatible content.',
            'content_date.date_format' => 'The content date does not match the format YYYY-MM-DD.',
            'application_date.date_format' => 'The application date does not match the format YYYY-MM-DD.',
            'end_date_account_restriction.date_format' => 'The end date of account restriction does not match the format YYYY-MM-DD.',
            'end_date_monetary_restriction.date_format' => 'The end date of monetary restriction does not match the format YYYY-MM-DD.',
            'end_date_service_restriction.date_format' => 'The end date of service restriction does not match the format YYYY-MM-DD.',
            'end_date_visibility_restriction.date_format' => 'The end date of visibility restriction does not match the format YYYY-MM-DD.',
        ];
    }

    /**
     * @return array
     */
    public function buildOutputJsonResponse(
        mixed $payload_statement,
        Carbon $now,
        array $out

    ): array {
        $original = $payload_statement;

        $this->initArrayFields($original);
        $this->initOptionalFields($original);
        $this->removeHiddenFields($original);
        $original['platform_name'] = auth()->user()->platform->name;
        $original['created_at'] = $now->format('Y-m-d H:i:s');
        $out[] = $original;
        return $out;
    }

    /**
     * @param $statements
     * @param int|null $platform_id
     * @param $user_id
     * @return array
     */
    public function enrichThePayloadForBulkInsert(
        &$statements,
        ?int $platform_id,
        $user_id,
        string $method,
        StatementMultipleAPIController $statementMultipleAPIController
    ): array {
// enrich the payload for bulk insert.
        $now = Carbon::now();
        $out = [];
        $uuids = [];

        foreach ($statements as &$payload_statement) {
            $uuid = Str::uuid();
            $uuids[] = $uuid;
            $payload_statement['platform_id'] = $platform_id;
            $payload_statement['user_id'] = $user_id;
            $payload_statement['method'] = $method;
            $payload_statement['uuid'] = $uuid;
            $payload_statement['created_at'] = $now;
            $payload_statement['updated_at'] = $now;

            $out = $this->buildOutputJsonResponse($payload_statement,
                $now, $out);

            $this->sanitizePayloadStatement($payload_statement);

        }

        unset($payload_statement);
        return $out;
    }
}

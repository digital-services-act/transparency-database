<?php

namespace App\Http\Requests;

use App\Models\Statement;
use App\Services\EuropeanCountriesService;
use App\Services\EuropeanLanguagesService;
use Illuminate\Foundation\Http\FormRequest;

class StatementSearchRequest extends FormRequest
{
    protected array $advancedFilters =  [
        'account_type',
        'category_specification',
        'territorial_scope',
        'content_type',
        'content_language',
        'automated_detection',
        'automated_decision',
        'created_at_start',
        'created_at_end',
    ];

    public function hasAdvancedFilters(): bool
    {
        foreach ($this->advancedFilters as $filter) {
            if ($this->filled($filter)) {
                return true;
            }
        }
        // @codeCoverageIgnoreStart
        return false;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(EuropeanLanguagesService $langService): array
    {
        return [
            's' => ['nullable', 'string', 'max:255'],

            'platform_id' => ['nullable', 'array'],
            'platform_id.*' => ['integer'],

            'decision_ground' => ['nullable', 'array'],
            'decision_ground.*' => ['string'],

            'source_type' => ['nullable', 'array'],
            'source_type.*' => ['string'],

            'category' => ['nullable', 'array'],
            'category.*' => ['string'],

            'decision_visibility' => ['nullable', 'array'],
            'decision_visibility.*' => ['string'],

            'decision_monetary' => ['nullable', 'array'],
            'decision_monetary.*' => ['string'],

            'decision_provision' => ['nullable', 'array'],
            'decision_provision.*' => ['string'],

            'decision_account' => ['nullable', 'array'],
            'decision_account.*' => ['string'],

            'account_type' => ['nullable', 'array'],
            'account_type.*' => ['string'],

            'category_specification' => ['nullable', 'array'],
            'category_specification.*' => ['string'],

            'created_at_start' => ['nullable', 'date'],
            'created_at_end' => ['nullable', 'date', 'after_or_equal:created_at_start'],

            'territorial_scope' => ['nullable', 'array'],
            'territorial_scope.*' => ['string'],

            'content_type' => ['nullable', 'array'],
            'content_type.*' => ['string'],

            'content_language' => ['nullable', 'array'],
            'content_language.*' => ['string', 'in:' . implode(',', array_keys($langService->getAllLanguages()))],

            'automated_detection' => ['nullable', 'array'],
            'automated_detection.*' => ['string'],

            'automated_decision' => ['nullable', 'array'],
            'automated_decision.*' => ['string'],
        ];
    }

    public function messages(): array
    {
        return [
            'platform_id.*.integer' => 'Invalid platform selected.',
            'content_type.*' => 'Invalid content type selected.',
            'content_language.*' => 'Invalid content language selected.',
            'created_at_end.after_or_equal' => 'End date must be after start date.',
        ];
    }

    public function prepareForValidation(): void
    {
        $countriesService = new EuropeanCountriesService();
        $langService = new EuropeanLanguagesService();

        $this->merge([
            'decision_ground' => array_values(array_intersect(
                (array) $this->decision_ground,
                array_keys(Statement::DECISION_GROUNDS)
            )),
            'source_type' => array_values(array_intersect(
                (array) $this->source_type,
                array_keys(Statement::SOURCE_TYPES)
            )),
            'category' => array_values(array_intersect(
                (array) $this->category,
                array_keys(Statement::STATEMENT_CATEGORIES)
            )),
            'decision_monetary' => array_values(array_intersect(
                (array) $this->decision_monetary,
                array_keys(Statement::DECISION_MONETARIES)
            )),
            'decision_provision' => array_values(array_intersect(
                (array) $this->decision_provision,
                array_keys(Statement::DECISION_PROVISIONS)
            )),
            'decision_account' => array_values(array_intersect(
                (array) $this->decision_account,
                array_keys(Statement::DECISION_ACCOUNTS)
            )),
            'account_type' => array_values(array_intersect(
                (array) $this->account_type,
                array_keys(Statement::ACCOUNT_TYPES)
            )),
            'decision_visibility' => array_values(array_intersect(
                (array) $this->decision_visibility,
                array_keys(Statement::DECISION_VISIBILITIES)
            )),
            'category_specification' => array_values(array_intersect(
                (array) $this->category_specification,
                array_keys(Statement::KEYWORDS)
            )),
            'territorial_scope' => array_values(array_intersect(
                (array) $this->territorial_scope,
                array_keys($countriesService->getOptionsArray())
            )),
            'content_type' => array_values(array_intersect(
                (array) $this->content_type,
                array_keys(Statement::CONTENT_TYPES)
            )),
            'content_language' => array_values(array_intersect(
                (array) $this->content_language,
                array_keys($langService->getAllLanguages())
            )),
            'automated_detection' => array_values(array_intersect(
                (array) $this->automated_detection,
                array_keys(Statement::AUTOMATED_DETECTIONS)
            )),
            'automated_decision' => array_values(array_intersect(
                (array) $this->automated_decision,
                array_keys(Statement::AUTOMATED_DECISIONS)
            )),
        ]);
    }
}

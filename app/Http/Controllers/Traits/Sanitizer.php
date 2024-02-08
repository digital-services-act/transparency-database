<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Arr;

trait Sanitizer
{
    /**
     * @return array
     */
    public function sanitizeData(array $validated): array
    {
        $validated['application_date'] = $this->sanitizeDate($validated['application_date'] ?? null);
        $validated['content_date'] = $this->sanitizeDate($validated['content_date'] ?? null);
        $validated['end_date_monetary_restriction'] = $this->sanitizeDate($validated['end_date_monetary_restriction'] ?? null);
        $validated['end_date_visibility_restriction'] = $this->sanitizeDate($validated['end_date_visibility_restriction'] ?? null);
        $validated['end_date_account_restriction'] = $this->sanitizeDate($validated['end_date_account_restriction'] ?? null);
        $validated['end_date_service_restriction'] = $this->sanitizeDate($validated['end_date_service_restriction'] ?? null);

        $validated['territorial_scope'] = $this->european_countries_service->filterSortEuropeanCountries($validated['territorial_scope'] ?? []);
        $validated['content_type'] = array_unique($validated['content_type']);
        sort($validated['content_type']);
        if (array_key_exists('decision_visibility', $validated)) {
            $validated['decision_visibility'] = array_unique($validated['decision_visibility']);
            sort($validated['decision_visibility']);
        }

        if (array_key_exists('category_specification', $validated)) {
            $validated['category_specification'] = array_unique($validated['category_specification']);
            sort($validated['category_specification']);
        }

        if (array_key_exists('category_addition', $validated)) {
            $valueToRemove = $validated['category'];

            $collection = collect($validated['category_addition']);
            $filteredCollection = $collection->filter(static fn($item) => $item !== $valueToRemove);

            $filteredArray = $filteredCollection->toArray();

            $validated['category_addition'] = array_values($filteredArray);
        }

        return $validated;
    }
}

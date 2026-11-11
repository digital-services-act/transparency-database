<?php

namespace App\Rules;

final class StatementDateRules
{
    private const MINIMUM_DATES = [
        'content_date' => '2015-01-01',
        'application_date' => '2023-09-25',
    ];

    public static function rulesFor(string $field): array
    {
        $minimum = self::MINIMUM_DATES[$field];
        $maximum = now()->addHours(24)->toDateString();

        return [
            'bail',
            'required',
            'date_format:Y-m-d',
            'after_or_equal:'.$minimum,
            'before_or_equal:'.$maximum,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'content_date.after_or_equal' => 'The content date must be on or after 2015-01-01.',
            'content_date.before_or_equal' => 'The content date must be no later than 24 hours from now.',
            'application_date.after_or_equal' => 'The application date must be on or after 2023-09-25.',
            'application_date.before_or_equal' => 'The application date must be no later than 24 hours from now.',
        ];
    }
}

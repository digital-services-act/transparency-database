<?php

namespace App\Services;

use App\Models\Platform;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Intl\Countries;

class EuropeanCountriesService
{
    public const EUROPEAN_COUNTRY_CODES = [
        'AT',
        'BE',
        'BG',
        'CY',
        'CZ',
        'DE',
        'DK',
        'EE',
        'ES',
        'FI',
        'FR',
        'GR',
        'HR',
        'HU',
        'IE',
        'IS',
        'IT',
        'LI',
        'LT',
        'LU',
        'LV',
        'MT',
        'NL',
        'NO',
        'PL',
        'PT',
        'RO',
        'SE',
        'SI',
        'SK'
    ];

    public const EUROPEAN_ECONOMIC_AREA_COUNTRY_CODES = [
        'AT',
        'BE',
        'BG',
        'CY',
        'CZ',
        'DE',
        'DK',
        'EE',
        'ES',
        'FI',
        'FR',
        'GR',
        'HR',
        'HU',
        'IE',
        'IS',
        'IT',
        'LI',
        'LT',
        'LU',
        'LV',
        'MT',
        'NL',
        'NO',
        'PL',
        'PT',
        'RO',
        'SE',
        'SI',
        'SK'
    ];

    public const EUROPEAN_UNION_COUNTRY_CODES = [
        'AT',
        'BE',
        'BG',
        'CY',
        'CZ',
        'DE',
        'DK',
        'EE',
        'ES',
        'FI',
        'FR',
        'GR',
        'HR',
        'HU',
        'IE',
        'IT',
        'LT',
        'LU',
        'LV',
        'MT',
        'NL',
        'PL',
        'PT',
        'RO',
        'SE',
        'SI',
        'SK'
    ];

    public function getCountryName(string $iso): bool | string
    {
        if (in_array($iso, self::EUROPEAN_COUNTRY_CODES)) {
            try {
                return Countries::getName($iso);
            } catch (\Exception $e) {
                Log::error('European country code problem, could not convert "' . $iso .'"');
                return false; // should never happen but ...
            }
        }

        return false;
    }

    public function getCountryNames(array $countries, bool $condense = true): array
    {
        if (count($countries) === 0) {
            return [];
        }

        sort($countries);

        if ($countries === self::EUROPEAN_UNION_COUNTRY_CODES && $condense) {
            return ['European Union'];
        }

        if ($countries === self::EUROPEAN_ECONOMIC_AREA_COUNTRY_CODES && $condense) {
            return ['European Economic Area'];
        }

        $countries = array_intersect($countries, self::EUROPEAN_COUNTRY_CODES);
        $out = [];
        foreach ($countries as $iso) {
            $out[] = $this->getCountryName($iso);
        }

        return $out;
    }

    public function getOptionsArray(): array
    {
        return array_combine(self::EUROPEAN_COUNTRY_CODES, $this->getCountryNames(self::EUROPEAN_COUNTRY_CODES, false));
    }

    public function filterEuropeanCountries(array $countries): array
    {
        return array_intersect($countries, self::EUROPEAN_COUNTRY_CODES);
    }
}
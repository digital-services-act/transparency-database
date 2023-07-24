<?php

namespace App\Http\Controllers;

use App\Models\Statement;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\Intl\Countries;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param $array
     *
     * @return array
     */
    protected function mapForSelectWithoutKeys($array): array
    {
        return array_map(function ($value) {
            return ['value' => $value, 'label' => $value];
        }, $array);
    }

    /**
     * @param $array
     *
     * @return array
     */
    protected function mapForSelectWithKeys($array): array
    {
        return array_map(function ($key, $value) {
            return ['value' => $key, 'label' => $value];
        }, array_keys($array), array_values($array));
    }

    /**
     * @return string[]
     */
    protected function getEuropeanCountriesList(): array
    {
        $european_country_codes = Statement::EUROPEAN_COUNTRY_CODES;
        $european_countries_list = [];
        foreach ($european_country_codes as $iso) {
            $european_countries_list[$iso] = $iso === 'EEA' ? 'European Economic Area' : ($iso === 'EU' ? 'European Union' : Countries::getName($iso));
        }

        return $european_countries_list;
    }
}

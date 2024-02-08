<?php

namespace App\Http\Controllers;

use App\Models\Statement;
use App\Services\EuropeanCountriesService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Carbon;
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
        return array_map(fn($value) => ['value' => $value, 'label' => $value], $array);
    }

    protected function sanitizeDate($date): ?string
    {
        return $date ? Carbon::createFromFormat('Y-m-d', $date)->format('Y-m-d 00:00:00') : null;
    }

    /**
     * @param $array
     *
     * @return array
     */
    protected function mapForSelectWithKeys($array): array
    {
        return array_map(fn($key, $value) => ['value' => $key, 'label' => $value], array_keys($array), array_values($array));
    }
}

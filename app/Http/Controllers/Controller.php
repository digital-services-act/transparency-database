<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Carbon;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;
    /**
     * @param $array
     *
     * @return array
     */
    protected function mapForSelectWithoutKeys($array, bool $noval = false): array
    {
        $result = array_map(static fn($value) => ['value' => $value, 'label' => $value], $array);
        if ($noval)
        {
            array_unshift($result, ['value' => '--noval--', 'label' => 'None Specified']);
        }
        return $result;
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
    protected function mapForSelectWithKeys($array, bool $noval = false): array
    {
        $result = array_map(static fn($key, $value) => ['value' => $key, 'label' => $value], array_keys($array), array_values($array));
        if ($noval)
        {
            array_unshift($result, ['value' => '--noval--', 'label' => 'None Specified']);
        }
        return $result;
    }
}

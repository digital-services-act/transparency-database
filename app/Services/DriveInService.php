<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;

class DriveInService
{
    /**
     * @param $word
     *
     * @return array
     */
    public function getSimilarityWords($word): array
    {
        // Actual Request
        $payload = new stdClass();
        $payload->service = 'similarity';
        $payload->text = $word;
        $payload->parameters = new stdClass();
        $payload->parameters->lang = 'en';

        $payload = json_encode($payload);

        $headers = [];
        $headers['x-api-key'] = config('services.drivein.key');
        $headers['Accept'] = 'application/json';

        $response = Http::withHeaders($headers)
                        ->withBody($payload, 'application/json')
                        ->post(config('services.drivein.base'));

        $similarity_results = $response->json('result');

        return array_map(function($item){
            return str_replace("_", " ", $item);
        }, $similarity_results);
    }
}
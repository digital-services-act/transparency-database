<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenSearch\Client;

class SearchAPIController extends Controller
{
    public function passThrough(Request $request): callable|array
    {
        /** @var Client $client */
        $client = app(Client::class);
        $index_name = 'statement_' . config('app.env');

        $payload_in = $request->toArray();

        return $client->search([
            'index' => $index_name,
            'body' => $payload_in,
            'track_total_hits' => true
        ]);
    }
}
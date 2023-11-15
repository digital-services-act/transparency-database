<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenSearch\Client;

class SearchAPIController extends Controller
{
    public function search(Request $request): callable|array
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

    public function count(Request $request): callable|array
    {
        /** @var Client $client */
        $client = app(Client::class);
        $index_name = 'statement_' . config('app.env');

        $payload_in = $request->toArray();

        return $client->count([
            'index' => $index_name,
            'body' => $payload_in,
        ]);
    }

    public function sql(Request $request): callable|array
    {
        /** @var Client $client */
        $client = app(Client::class);
        $payload_in = $request->toArray();
        return $client->sql()->query($payload_in);
    }
}
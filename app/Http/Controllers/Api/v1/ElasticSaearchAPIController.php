<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ApiLoggingTrait;
use App\Http\Controllers\Traits\ExceptionHandlingTrait;
use Elastic\Elasticsearch\Client;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @codeCoverageIgnore This is an admin WIP route and thus we are not going to worry about coverage
 */
class ElasticSaearchAPIController extends Controller
{
    

    private Client $client;

    private string $index_name = 'search-statements-index';

    private int $error_code = Response::HTTP_UNPROCESSABLE_ENTITY;

    private int $response_size_limit = 5242880;

    public function __construct()
    {
        // $this->client = \Elastic\Elasticsearch\ClientBuilder::create()
        //     ->setHosts(explode(',', config('scout.elasticsearch.hosts')))
        //     //->setApiKey(config('scout.elasticsearch.apiKey'))
        //     ->setBasicAuthentication(
        //         config('scout.elasticsearch.basicAuthentication.username'),
        //         config('scout.elasticsearch.basicAuthentication.password')
        //     )
        //     ->build();
        
    }

    public function indices(Request $request)
    {
        $this->client = \Elastic\Elasticsearch\ClientBuilder::create()
            ->setHosts(config('scout.elasticsearch.hosts'))
            ->setApiKey(config('scout.elasticsearch.apiKey'))
            // ->setBasicAuthentication(
            //     config('scout.elasticsearch.basicAuthentication.username'),
            //     config('scout.elasticsearch.basicAuthentication.password')
            // )
            ->build();   
            return $this->client->cat()->indices([
                'format' => 'json',
                'index' => '*',
            ]);
    }

    /**
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        return $this->handleApiOperation(
            $request,
            function () use ($request) {
                try {
                    return response()->json($this->client->search([
                        'index' => $this->index_name,
                        'body' => $request->toArray(),
                    ]));
                } catch (Exception $exception) {
                    return response()->json(['error' => 'invalid query attempt: ' . $exception->getMessage()], $this->error_code);
                }
            }
        );
    }
}
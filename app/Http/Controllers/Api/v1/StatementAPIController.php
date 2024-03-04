<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ExceptionHandlingTrait;
use App\Http\Controllers\Traits\Sanitizer;
use App\Http\Controllers\Traits\StatementAPITrait;
use App\Http\Requests\StatementStoreRequest;
use App\Models\ArchivedStatement;
use App\Models\Statement;
use App\Services\EuropeanCountriesService;
use App\Services\GroupedSubmissionsService;
use App\Services\PlatformUniqueIdService;
use App\Services\StatementSearchService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;


class StatementAPIController extends Controller
{
    use Sanitizer;
    use ExceptionHandlingTrait;
    use StatementAPITrait;

    protected EuropeanCountriesService $european_countries_service;
    protected PlatformUniqueIdService $platform_unique_id_service;
    protected StatementSearchService $statement_search_service;
    protected GroupedSubmissionsService $grouped_submissions_service;

    public function __construct(
        EuropeanCountriesService $european_countries_service,
        StatementSearchService $statement_search_service,
        PlatformUniqueIdService $platform_unique_id_service
    ) {
        $this->european_countries_service = $european_countries_service;
        $this->statement_search_service = $statement_search_service;
        $this->platform_unique_id_service = $platform_unique_id_service;
    }

    public function show(Statement $statement): Statement
    {
        return $statement;
    }

    public function existingPuid(Request $request, string $puid): JsonResponse
    {
        //TODO: query from opensearch or index
        $platform_id = $this->getRequestUserPlatformId($request);

        $statement = Statement::query()->where('puid', $puid)->where('platform_id', $platform_id)->first();
        if ($statement) {
            return response()->json($statement, Response::HTTP_FOUND);
        }

        return response()->json(['message' => 'statement of reason not found'], Response::HTTP_NOT_FOUND);
    }

    public function store(StatementStoreRequest $request): JsonResponse
    {
        $validated = $request->safe()->merge(
            [
                'platform_id' => $this->getRequestUserPlatformId($request),
                'user_id' => $request->user()->id,
                'method' => Statement::METHOD_API,
            ]
        )->toArray();

        $validated = $this->sanitizeData($validated);

        $alreadyInCache = $this->platform_unique_id_service->addPuidToCache($validated['platform_id'],
            $validated['puid'], $this);

        //Cache Protection
        if (!$alreadyInCache) {
            Cache::increment($this->platform_unique_id_service->getCacheKey($validated['platform_id'],
                $validated['puid']));
            $errors = [
                'puid' => [
                    'The identifier given is not unique within this platform.'
                ]
            ];
            $message = 'The identifier given is not unique within this platform.';

            $out = ['message' => $message, 'errors' => $errors, 'existing' => (object)['puid' => $validated['puid']]];

            return response()->json($out, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        //Database Protection
        try {
            $this->platform_unique_id_service->addPuidToDatabase($validated['platform_id'], $validated['puid']);
        } catch (QueryException $e) {
            if (
                str_contains($e->getMessage(), "platform_puid_unique") || // mysql
                str_contains($e->getMessage(),
                    "UNIQUE constraint failed: archived_statements.platform_id, archived_statements.puid") // sqlite
            ) {
                $errors = [
                    'puid' => [
                        'The identifier given is not unique within this platform.'
                    ]
                ];
                $message = 'The identifier given is not unique within this platform.';

                $out = ['message' => $message, 'errors' => $errors];

                return response()->json($out, Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            return $this->handleQueryException($e, 'Statement');
        }

        try {
            $statement = Statement::create($validated);
        } catch (QueryException $queryException) {
            return $this->handleQueryException($queryException, 'Statement');
        }


        $out = $statement->toArray();
        $out['puid'] = $statement->puid; // Show the puid on a store.


        return response()->json($out, Response::HTTP_CREATED);
    }


}

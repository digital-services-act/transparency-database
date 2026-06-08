<?php

namespace App\Http\Controllers\Api\v1;

use App\Exceptions\PuidNotUniqueSingleException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ExceptionHandlingTrait;
use App\Http\Controllers\Traits\Sanitizer;
use App\Http\Controllers\Traits\StatementAPITrait;
use App\Http\Requests\StatementStoreRequest;
use App\Models\Statement;
use App\Services\EuropeanCountriesService;
use App\Services\PlatformUniqueIdService;
use App\Services\StatementElasticConnectionService;
use App\Services\StatementElasticIndexerService;
use App\Services\StatementElasticSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StatementAPIController extends Controller
{
    use ExceptionHandlingTrait;
    use Sanitizer;
    use StatementAPITrait;

    protected EuropeanCountriesService $european_countries_service;

    public function __construct(
        EuropeanCountriesService $european_countries_service,
        protected StatementElasticSearchService $statement_elastic_search_service,
        protected PlatformUniqueIdService $puidService

    ) {
        $this->european_countries_service = $european_countries_service;
    }

    public function show(Statement $statement): Statement
    {
        return $statement;
    }

    public function existingPuid(Request $request, string $puid): Statement|JsonResponse
    {
        $platform_id = $this->getRequestUserPlatformId($request);

        $found = $this->puidService->checkPuidExists($platform_id, $puid);

        if ($found || $this->statement_elastic_search_service->PlatformIdPuidToId($platform_id, $puid) !== 0) {
            // Return a minimal statement object with just the PUID when found in cache/database but not in OpenSearch
            return response()->json(['message' => 'statement of reason found', 'puid' => $puid], Response::HTTP_FOUND);
        }

        return response()->json(
            ['message' => 'statement of reason not found', 'puid' => $puid],
            Response::HTTP_NOT_FOUND
        );
    }

    public function store(
        StatementStoreRequest $request,
        StatementElasticConnectionService $statement_elastic_connection_service,
        StatementElasticIndexerService $statement_elastic_indexer_service,
    ): JsonResponse {
        $validated = $request->safe()->merge(
            [
                'platform_id' => $this->getRequestUserPlatformId($request),
                'user_id' => $request->user()->id,
                'method' => Statement::METHOD_API,
            ]
        )->toArray();

        $validated = $this->sanitizeData($validated);

        // Extract EAN-13 codes from content_id if present and no content_id_ean is present
        if (! isset($validated['content_id_ean']) && isset($validated['content_id']) && isset($validated['content_id']['EAN-13'])) {
            $validated['content_id_ean'] = $validated['content_id']['EAN-13'];
        }

        try {
            $statement = $this->puidService->runWithReservedPuid(
                $validated['platform_id'],
                $validated['puid'],
                static fn () => Statement::create($validated)
            );
        } catch (PuidNotUniqueSingleException $e) {
            return $e->getJsonResponse();
        }

        $out = $statement->toArray();
        $out['puid'] = $statement->puid;

        $env = config('app.env');
        if ($env !== 'production' && $statement_elastic_connection_service->isConfigured()) {
            // If we are not production and
            // If we have elasticsearch configured, we want to index the new statement
            // right away so it appears in search results immediately.
            $statement_elastic_indexer_service->indexStatement($statement);
        }

        return response()->json($out, Response::HTTP_CREATED);
    }
}

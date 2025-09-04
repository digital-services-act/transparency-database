<?php

namespace App\Http\Controllers\Api\v1;

use App\Exceptions\PuidNotUniqueSingleException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ExceptionHandlingTrait;
use App\Http\Controllers\Traits\Sanitizer;
use App\Http\Controllers\Traits\StatementAPITrait;
use App\Http\Requests\StatementStoreRequest;
use App\Models\PlatformPuid;
use App\Models\Statement;
use App\Services\EuropeanCountriesService;
use App\Services\PlatformUniqueIdService;
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
        protected PlatformUniqueIdService $platform_unique_id_service
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

        $found = false;
        // First check if PUID exists in cache or in the PlatformPuid Table
        if ($this->platform_unique_id_service->isPuidInCache($platform_id, $puid) || PlatformPuid::where('platform_id',
            $platform_id)->where('puid', $puid)->exists()) {
            $found = true;
        }

        if ($found || $this->statement_elastic_search_service->PlatformIdPuidToId($platform_id, $puid) !== 0) {
            // Return a minimal statement object with just the PUID when found in cache/database but not in OpenSearch
            return response()->json(['message' => 'statement of reason found', 'puid' => $puid], Response::HTTP_FOUND);
        }

        return response()->json(['message' => 'statement of reason not found', 'puid' => $puid],
            Response::HTTP_NOT_FOUND);
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

        // Extract EAN-13 codes from content_id if present and no content_id_ean is present
        if (! isset($validated['content_id_ean']) && isset($validated['content_id']) && isset($validated['content_id']['EAN-13'])) {
            $validated['content_id_ean'] = $validated['content_id']['EAN-13'];
        }

        try {
            $this->platform_unique_id_service->addPuidToCache($validated['platform_id'], $validated['puid']);
            $this->platform_unique_id_service->addPuidToDatabase($validated['platform_id'], $validated['puid']);
        } catch (PuidNotUniqueSingleException $e) {
            return $e->getJsonResponse();
        }

        $statement = Statement::create($validated);
        $out = $statement->toArray();
        $out['puid'] = $statement->puid;

        $uri = config('elasticsearch.uri');
        $env = config('app.env');
        if ($env !== 'production' && $uri && $uri[0]) {
            // If we are not production and
            // If we have elasticsearch configured, we want to index the new statement
            // right away so it appears in search results immediately.
            $this->statement_elastic_search_service->indexStatement($statement);
        }

        return response()->json($out, Response::HTTP_CREATED);
    }
}

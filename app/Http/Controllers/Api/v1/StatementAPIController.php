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
use App\Services\StatementSearchService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;


class StatementAPIController extends Controller
{
    use Sanitizer;
    use ExceptionHandlingTrait;
    use StatementAPITrait;

    protected EuropeanCountriesService $european_countries_service;
    protected PlatformUniqueIdService $platform_unique_id_service;
    protected StatementSearchService $statement_search_service;

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

    public function showUuid(string $uuid
    ): \Illuminate\Contracts\Foundation\Application|Application|RedirectResponse|Redirector|JsonResponse {
        $id = $this->statement_search_service->uuidToId($uuid);
        if ($id === 0) {
            return response()->json(['message' => 'statement of reason not found'], Response::HTTP_NOT_FOUND);
        }
        return redirect(route('api.v1.statement.show', [$id]));
    }

    public function existingPuid(Request $request, string $puid): Statement|JsonResponse
    {
        $platform_id = $this->getRequestUserPlatformId($request);

        $id = $this->statement_search_service->PlatformIdPuidToId($platform_id, $puid);
        if ($id === 0) {
            return response()->json(['message' => 'statement of reason not found'], Response::HTTP_NOT_FOUND);
        }
        $statement = Statement::find($id);
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

        try {
            $this->platform_unique_id_service->addPuidToCache($validated['platform_id'], $validated['puid']);
            $this->platform_unique_id_service->addPuidToDatabase($validated['platform_id'], $validated['puid']);
        } catch (PuidNotUniqueSingleException $e) {
            return $e->getJsonResponse();
        } catch (QueryException $queryException) {
            return $this->handleQueryException($queryException, 'Statement');
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

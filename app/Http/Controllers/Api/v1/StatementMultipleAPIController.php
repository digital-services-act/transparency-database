<?php

namespace App\Http\Controllers\Api\v1;

use App\Exceptions\PuidNotUniqueMultipleException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ExceptionHandlingTrait;
use App\Http\Controllers\Traits\Sanitizer;
use App\Http\Controllers\Traits\StatementAPITrait;
use App\Models\Statement;
use App\Services\EuropeanCountriesService;
use App\Services\GroupedSubmissionsService;
use App\Services\PlatformUniqueIdService;
use App\Services\StatementSearchService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class StatementMultipleAPIController extends Controller
{
    use Sanitizer;
    use ExceptionHandlingTrait;
    use StatementAPITrait;

    protected EuropeanCountriesService $european_countries_service;


    protected StatementSearchService $statement_search_service;


    public function __construct(protected PlatformUniqueIdService $platform_unique_id_service, protected GroupedSubmissionsService $grouped_submissions_service)
    {
    }


    /**
     * @throws PuidNotUniqueMultipleException
     */
    public function store(Request $request): JsonResponse
    {
        if (!$request->user()->platform || !$request->user()->can('create statements')) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $platform_id = $this->getRequestUserPlatformId($request);
        $user_id = $request->user()->id;
        $method = Statement::METHOD_API_MULTI;

        $payload = $request->validate([
            'statements' => 'required|array|between:1,100',
        ]);

        $errors = [];
        [$errors, $payload] = $this->grouped_submissions_service->sanitizePayload($payload, $errors);
        if ($errors !== []) {
            // Return validation errors as a JSON response
            Log::info('Statement Multiple Store Request Validation Failure', [
                'request' => $request->all(),
                'errors' => $errors,
                'user' => auth()->user()->id ?? -1,
                'user_email' => auth()->user()->email ?? 'n/a',
                'platform' => auth()->user()->platform->name ?? 'no platform'
            ]);

            return response()->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Check if PUIDs are unique in the Request made by the client
        $puids_to_check = array_map(static fn($potential_statement) => $potential_statement['puid'],
            $payload['statements']);

        try {
            $this->platform_unique_id_service->checkDuplicatesInRequest($puids_to_check);
            $this->platform_unique_id_service->checkDuplicatesInCache($puids_to_check, $platform_id);
            $this->platform_unique_id_service->checkDuplicatesInArchivedStatement($puids_to_check, $platform_id);
        } catch (PuidNotUniqueMultipleException $puidNotUniqueMultipleException) {
            // If the cache expired, and we got a new duplicate, we add it again to the cache
            $this->platform_unique_id_service->refreshPuidsInCache($puidNotUniqueMultipleException->getDuplicates(), $platform_id);
            return $puidNotUniqueMultipleException->getJsonResponse();
        }

        $out = $this->grouped_submissions_service->enrichThePayloadForBulkInsert($payload['statements'], $platform_id,
            $user_id, $method, $this);

        try {
            // Bulk Insert
            Statement::insert($payload['statements']);

            //No error, add the platform unique ids into the cache and database
            foreach ($payload['statements'] as $statement) {
                $this->platform_unique_id_service->addPuidToCache($statement['platform_id'], $statement['puid']);
                $this->platform_unique_id_service->addPuidToDatabase($statement['platform_id'], $statement['puid']);
            }

            return response()->json(['statements' => $out], Response::HTTP_CREATED);
        } catch (QueryException $queryException) {
            return $this->handleQueryException($queryException, 'Statement');
        }
    }


}

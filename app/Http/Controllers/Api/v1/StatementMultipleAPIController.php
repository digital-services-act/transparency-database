<?php

namespace App\Http\Controllers\Api\v1;

use App\Exceptions\PuidNotUniqueMultipleException;
use App\Exceptions\PuidNotUniqueSingleException;
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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use JsonException;

class StatementMultipleAPIController extends Controller
{
    use Sanitizer;
    use ExceptionHandlingTrait;
    use StatementAPITrait;

    public function __construct(
        protected PlatformUniqueIdService $platform_unique_id_service,
        protected GroupedSubmissionsService $grouped_submissions_service,
        protected StatementSearchService $statement_search_service,
        protected EuropeanCountriesService $european_countries_service
    ) {
    }


    /**
     * @throws PuidNotUniqueMultipleException|PuidNotUniqueSingleException|JsonException
     */
    public function store(Request $request): JsonResponse
    {
        $platform_id = $this->getRequestUserPlatformId($request);
        $user_id = $request->user()->id;
        $method = Statement::METHOD_API_MULTI;

        $payload = $request->validate([
            'statements' => 'required|array|between:1,100',
        ]);

        $errors = [];
        [$errors, $payload] = $this->grouped_submissions_service->sanitizePayload($payload, $errors);
        if ($errors !== []) {
            if (Cache::get('validation_failure_logging', true)) {
                // Return validation errors as a JSON response
                Log::info('Statement Multiple Store Request Validation Failure', [
                    'request' => $request->all(),
                    'errors' => $errors,
                    'user' => auth()->user()->id ?? -1,
                    'user_email' => auth()->user()->email ?? 'n/a',
                    'platform' => auth()->user()->platform->name ?? 'no platform'
                ]);
            }

            return response()->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Check if PUIDs are unique in the Request made by the client
        $puids_to_check = array_map(static fn($potential_statement) => $potential_statement['puid'],
            $payload['statements']);

        try {
            $this->platform_unique_id_service->checkDuplicatesInRequest($puids_to_check);
            $this->platform_unique_id_service->checkDuplicatesInCache($puids_to_check, $platform_id);
            $this->platform_unique_id_service->checkDuplicatesInPlatformPuids($puids_to_check, $platform_id);
        } catch (PuidNotUniqueMultipleException $puidNotUniqueMultipleException) {
            // If the cache expired, and we got a new duplicate, we add it again to the cache
            $this->platform_unique_id_service->refreshPuidsInCache($puidNotUniqueMultipleException->getDuplicates(),
                $platform_id);
            return $puidNotUniqueMultipleException->getJsonResponse();
        }

        $out = $this->grouped_submissions_service->enrichThePayloadForBulkInsert($payload['statements'], $platform_id,
            $user_id, $method);

        $this->insertAndAddPuidsToCacheAndDatabase($payload);
        
        return response()->json(['statements' => $out], Response::HTTP_CREATED);

    }

    /**
     * @codeCoverageIgnore We are ccovering this elsewhere
     * @param array $payload
     * @return void
     */
    private function insertAndAddPuidsToCacheAndDatabase(array $payload)
    {
        if (strtolower((string)config('app.env_real')) === 'production') {
            // Bulk insert on production, the cron will index later.
            Statement::insert($payload['statements']);
        } else {
            // Not production, we index at the moment.
            $id_before = Statement::query()->orderBy('id', 'DESC')->first()->id;
            Statement::insert($payload['statements']);
            $id_after = Statement::query()->orderBy('id', 'DESC')->first()->id;

            $statements = Statement::query()->where('id', '>=', $id_before)->where('id', '<=', $id_after)->get();
            $this->statement_search_service->bulkIndexStatements($statements);
        }


        //No error, add the platform unique ids into the cache and database
        foreach ($payload['statements'] as $statement) {
            try {
                $this->platform_unique_id_service->addPuidToCache($statement['platform_id'], $statement['puid']);
            } catch (PuidNotUniqueSingleException $puidNotUniqueSingleException) {
                Log::info('PUID Not Unique in Cache Exception thrown in Multiple Statements', [
                    'platform_id' => $statement['platform_id'],
                    'puid' => $statement['puid']
                ]);
            }

            try {
                $this->platform_unique_id_service->addPuidToDatabase($statement['platform_id'], $statement['puid']);
            } catch (PuidNotUniqueSingleException $puidNotUniqueSingleException) {
                Log::info('PUID Not Unique in Database Exception thrown in Multiple Statements', [
                    'platform_id' => $statement['platform_id'],
                    'puid' => $statement['puid']
                ]);
            }
        }
    }
}

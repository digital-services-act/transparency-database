<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\Sanitizer;
use App\Http\Requests\StatementStoreRequest;
use App\Jobs\StatementInsert;
use App\Models\Statement;
use App\Services\EuropeanCountriesService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StatementAPIController extends Controller
{
    use Sanitizer;

    protected EuropeanCountriesService $european_countries_service;
    public function __construct(
        EuropeanCountriesService $european_countries_service,
    )
    {
        $this->european_countries_service = $european_countries_service;
    }

    public function show(Statement $statement): Statement
    {
        return $statement;
    }

    public function existingPuid(Request $request, String $puid): JsonResponse
    {
        $platform_id = $request->user()->platform_id;

        $statement = Statement::query()->where('puid', $puid)->where('platform_id', $platform_id)->first();
        if ($statement) {
            return response()->json($statement, Response::HTTP_FOUND);
        }
        return response()->json(['message' => 'statement of reason not found'], Response::HTTP_NOT_FOUND);
    }

    public function store(StatementStoreRequest $request): JsonResponse
    {

        if (config('dsa.STATEMENT_INSERT') === 'queued') {
            return $this->storeDelayed($request);
        }

        $validated = $request->safe()->merge(
            [
                'platform_id' => $request->user()->platform_id,
                'user_id' => $request->user()->id,
                'method' => Statement::METHOD_API,
            ]
        )->toArray();

        $validated = $this->sanitizeData($validated);

        try {
            $statement = Statement::create($validated);
        } catch (QueryException $e) {
            if (
                str_contains($e->getMessage(), "statements_platform_id_puid_unique") || // mysql
                str_contains($e->getMessage(), "UNIQUE constraint failed: statements.platform_id, statements.puid") // sqlite
            ) {
                $errors = [
                    'puid' => [
                        'The identifier given is not unique within this platform.'
                    ]
                ];
                $message = 'The identifier given is not unique within this platform.';

                $out = ['message' => $message, 'errors' => $errors];
                $existing = Statement::query()->where('puid', $validated['puid'])->where('platform_id', $validated['platform_id'])->first();
                if ($existing) {
                    $out['existing'] = $existing;
                }

                return response()->json($out, Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            Log::error('Statement Creation Query Exception Thrown: ' . $e->getMessage());
            $errors  = [
                'uncaught_exception' => [
                    'Statement Creation Query Exception Thrown: ' . $e->getMessage()
                ]
            ];
            $message = 'Statement Creation Query Exception Thrown: ' . $e->getMessage();

            return response()->json(['message' => $message, 'errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }


        $out = $statement->toArray();
        $out['puid'] = $statement->puid; // Show the puid on a store.

        return response()->json($out, Response::HTTP_CREATED);
    }

    public function storeDelayed(StatementStoreRequest $request): JsonResponse
    {
        $uuid = Str::uuid();
        $validated = $request->safe()->merge(
            [
                'platform_id' => $request->user()->platform_id,
                'user_id' => $request->user()->id,
                'method' => Statement::METHOD_API,
                'uuid' => $uuid
            ]
        )->toArray();

        $validated = $this->sanitizeData($validated);


        // If in cache or in db stop!
        // Not in cache, not in db then go!

        $key = 'queued|' . $validated['platform_id'] . '|' . $validated['puid'];
        $existing_in_cache = Cache::get($key);
        if ($existing_in_cache) {
            $errors = [
                'puid' => [
                    'The identifier given is not unique within this platform.'
                ]
            ];
            $message = 'The identifier given is not unique within this platform.';
            $out = ['message' => $message, 'errors' => $errors, 'existing' => $existing_in_cache];
            return response()->json($out, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $existing_in_db = Statement::query()->where('platform_id', $validated['platform_id'])->where('puid', $validated['puid'])->first();
        if ($existing_in_db) {
            $errors = [
                'puid' => [
                    'The identifier given is not unique within this platform.'
                ]
            ];
            $message = 'The identifier given is not unique within this platform.';
            $out = ['message' => $message, 'errors' => $errors, 'existing' => $existing_in_db];
            return response()->json($out, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        StatementInsert::dispatch($validated);

        $out = $validated;
        $out['permalink'] = route('home') . '/statement/' . $uuid;
        $out['self'] = route('home') . '/api/v1/statement/' . $uuid;
        $out['created_at'] = date('Y-m-d H:i:s');

        unset($out['user_id'], $out['platform_id'], $out['method']);

        return response()->json($out, Response::HTTP_CREATED);
    }
}

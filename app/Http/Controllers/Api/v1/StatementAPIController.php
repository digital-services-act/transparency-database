<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\Sanitizer;
use App\Http\Requests\StatementsStoreRequest;
use App\Http\Requests\StatementStoreRequest;
use App\Models\Statement;
use App\Services\EuropeanCountriesService;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
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

    public function storeMultiple(StatementsStoreRequest $request): JsonResponse
    {
        $platform_id = $request->user()->platform_id;
        $user_id     = $request->user()->id;
        $method      = Statement::METHOD_API;

        $potential_statements = $request->validated();

        if ( ! is_array($potential_statements) || count($potential_statements) > 1000) {
            $errors  = [
                'statements' => [
                    'The body of the post needs to be an array of statement of reasons and no more than 1000.'
                ]
            ];
            $message = 'The body of the post needs to be an array of statement of reasons and no more than 1000.';
            $out     = ['message' => $message, 'errors' => $errors];

            return response()->json($out, Response::HTTP_UNPROCESSABLE_ENTITY);
        }


        $puids_to_check = array_map(static function ($potential_statement) {
            return $potential_statement['puid'];
        }, $potential_statements);

        $unique_puids_to_check = array_unique($puids_to_check);
        if (count($unique_puids_to_check) !== count($puids_to_check)) {
            $errors  = [
                'puid' => [
                    'The identifier(s) are not unique within this call.'
                ],
            ];
            $message = 'The identifier(s) are not unique within this call.';
            $out     = ['message' => $message, 'errors' => $errors];

            return response()->json($out, Response::HTTP_UNPROCESSABLE_ENTITY);
        }



        $existing = Statement::query()->where('platform_id', $platform_id)->whereIn('puid', $puids_to_check)->pluck('puid')->toArray();

        if (count($existing)) {
            $errors  = [
                'puid'           => [
                    'The identifier(s) are not unique within this platform.'
                ],
                'existing_puids' => $existing
            ];
            $message = 'The identifiers given are not unique within this platform.';
            $out     = ['message' => $message, 'errors' => $errors];

            return response()->json($out, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $now = Carbon::now();

        $uuids = [];
        foreach ($potential_statements as $index => $potential_statement) {
            $uuid                                        = Str::uuid();
            $uuids[]                                     = $uuid;
            $potential_statements[$index]['platform_id'] = $platform_id;
            $potential_statements[$index]['user_id']     = $user_id;
            $potential_statements[$index]['method']      = $method;
            $potential_statements[$index]['uuid']        = $uuid;
            $potential_statements[$index]['created_at']  = $now;
            $potential_statements[$index]['updated_at']  = $now;

            // stringify the arrays
            foreach ($potential_statements[$index] as $key => $value) {
                if (is_array($value)) {
                    $potential_statements[$index][$key] = '["' . implode('","', $value) . '"]';
                }
            }
        }

        try {

            Statement::insert($potential_statements);
            $created_statements = Statement::query()->whereIn('uuid', $uuids)->get();
            $created_statements->searchable();
            $out = [];
            foreach ($created_statements as $created_statement) {
                $puid = $created_statement->puid;
                $created_statement = $created_statement->toArray();
                $created_statement['puid'] = $puid;
                $out[] = $created_statement;
            }
            return response()->json($out, Response::HTTP_CREATED);

        } catch (Exception $e) {
            Log::error('Statement Creation Query Exception Thrown: ' . $e->getMessage());
            $errors = [
                'Statement Creation Query Exception Thrown'
            ];
            $message = 'Statement Creation Query Exception Thrown';
            return response()->json(['message' => $message, 'errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

}

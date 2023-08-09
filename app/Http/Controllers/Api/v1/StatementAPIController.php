<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StatementStoreRequest;
use App\Models\Statement;
use App\Services\EuropeanCountriesService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class StatementAPIController extends Controller
{
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

    public function store(StatementStoreRequest $request): JsonResponse
    {

        $validated = $request->safe()->merge(
            [
                'platform_id' => $request->user()->platform_id,
                'user_id' => $request->user()->id,
                'method' => Statement::METHOD_API,
            ]
        )->toArray();

        $validated['application_date'] = $this->sanitizeDate($validated['application_date'] ?? null);
        $validated['content_date'] = $this->sanitizeDate($validated['content_date'] ?? null);
        $validated['end_date'] = $this->sanitizeDate($validated['end_date'] ?? null);

        $validated['territorial_scope'] = $this->european_countries_service->filterSortEuropeanCountries($validated['territorial_scope'] ?? []);
        $validated['content_type'] = array_unique($validated['content_type']);
        sort($validated['content_type']);
        if(array_key_exists('decision_visibility',$validated)){
            $validated['decision_visibility'] = array_unique($validated['decision_visibility']);
            sort($validated['decision_visibility']);
        }



        try {
            $statement = Statement::create($validated);
        } catch (QueryException $e) {
            if (
                str_contains($e->getMessage(), "statements_platform_id_puid_unique")
            ) {
                $errors = [
                    'puid' => [
                        'The identifier given is not unique within this platform.'
                    ]
                ];
                $message = 'The identifier given is not unique within this platform.';

                return response()->json(['message' => $message, 'errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                Log::error('Statement Creation Query Exception Thrown: ' . $e->getMessage());
                $errors = [
                    'uncaught_exception' => [
                        'Statement Creation Query Exception Thrown: ' . $e->getMessage()
                    ]
                ];
                $message = 'Statement Creation Query Exception Thrown: ' . $e->getMessage();
                return response()->json(['message' => $message, 'errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }


        $out = $statement->toArray();
        $out['puid'] = $statement->puid; // Show the puid on a store.

        return response()->json($out, Response::HTTP_CREATED);
    }
}

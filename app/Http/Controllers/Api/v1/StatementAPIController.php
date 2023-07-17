<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StatementStoreRequest;
use App\Models\Statement;
use App\Services\StatementQueryService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class StatementAPIController extends Controller
{
    protected StatementQueryService $statement_query_service;

    public function __construct(StatementQueryService $statement_query_service)
    {
        $this->statement_query_service = $statement_query_service;
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

        try {
            $statement = Statement::create($validated);
        } catch (QueryException $e) {
            if (
                str_contains($e->getMessage(), 'Integrity constraint violation: 1062 Duplicate entry') &&
                str_contains($e->getMessage(), "for key 'statements_platform_id_puid_unique'")
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

        return response()->json($statement, Response::HTTP_CREATED);
    }

    public function search(Request $request): JsonResponse
    {
        $statements = $this->statement_query_service->query($request->query());
        $statements = $statements->orderBy('created_at', 'DESC')->paginate(50)->withQueryString();
        return response()->json($statements);
    }
}

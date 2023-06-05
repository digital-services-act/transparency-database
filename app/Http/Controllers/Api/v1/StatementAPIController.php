<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StatementStoreRequest;
use App\Models\Statement;
use App\Services\StatementQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
                'user_id' => auth()->user()->id,
                'method' => Statement::METHOD_API,
            ]
        )->toArray();

        $statement = Statement::create($validated);

        return response()->json($statement, Response::HTTP_CREATED);
    }

    public function search(Request $request): JsonResponse
    {
        $statements = $this->statement_query_service->query($request->query());
        $statements = $statements->orderBy('created_at', 'DESC')->paginate(50)->withQueryString();
        return response()->json($statements);
    }
}

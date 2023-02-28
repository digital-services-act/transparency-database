<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StatementStoreRequest;
use App\Models\Statement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class StatementAPIController extends Controller
{
    /**
     * @param Statement $statement
     *
     * @return Statement
     */
    public function show(Statement $statement): Statement
    {
        return $statement;
    }

    /**
     * @param StatementStoreRequest $request
     * @return JsonResponse
     */
    public function store(StatementStoreRequest $request): JsonResponse
    {
        $validated = $request->safe()->merge(
            [
                'user_id' => auth()->user()->id,
                'method' => Statement::METHOD_API,
            ]
        )->toArray();

        $statement = Statement::create($validated);

        return response()->json([
            'status' => true,
            'message' => "statement created successfully!",
            'statement' => $statement
        ], Response::HTTP_CREATED);
    }
}

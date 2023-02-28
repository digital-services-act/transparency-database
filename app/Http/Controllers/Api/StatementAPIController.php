<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StatementStoreRequest;
use App\Http\Resources\StatementResource;
use App\Models\Statement;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery\Matcher\Not;

class StatementAPIController extends Controller
{
    /**
     * @param Statement $statement
     *
     * @return Statement
     */
    public function show(Statement $statement)
    {
        return $statement;
    }

    /**
     * @param StatementStoreRequest $request
     * @return JsonResponse
     */
    public function store(StatementStoreRequest $request)
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

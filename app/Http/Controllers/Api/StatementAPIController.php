<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\statementStoreRequest;
use App\Http\Resources\statementResource;
use App\Models\Statement;
use App\Models\User;
use Illuminate\Http\Request;
use Mockery\Matcher\Not;

class StatementAPIController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $statements = Statement::with('entities')->paginate(50);

        return view('statement.index', compact('statements'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        return view('statement.create');
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Statement $statement
     * @return statementResource
     */
    public function show(Statement $statement)
    {
        return $statement;
    }

    /**
     * @param \App\Http\Requests\statementStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(statementStoreRequest $request)
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
            'message' => "statement Created successfully!",
            'statement' => $statement
        ], 200);
    }
}

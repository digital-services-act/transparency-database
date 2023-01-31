<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\NoticeStoreRequest;
use App\Http\Resources\NoticeResource;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Http\Request;
use Mockery\Matcher\Not;

class NoticeAPIController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $notices = Notice::with('entities')->paginate(50);

        return view('notice.index', compact('notices'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        return view('notice.create');
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Notice $notice
     * @return NoticeResource
     */
    public function show(Notice $notice)
    {
        return $notice;
    }

    /**
     * @param \App\Http\Requests\NoticeStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(NoticeStoreRequest $request)
    {
        $validated = $request->safe()->merge(
            [
                'user_id' => auth()->user()->id,
                'method' => Notice::METHOD_API,
            ]
        )->toArray();

        $notice = Notice::create($validated);

        return response()->json([
            'status' => true,
            'message' => "Notice Created successfully!",
            'notice' => $notice
        ], 200);
    }
}

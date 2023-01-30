<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\NoticeStoreRequest;
use App\Http\Resources\NoticeResource;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Http\Request;

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
    public function show($id)
    {
        return new NoticeResource(Notice::findOrFail($id)->load('entities'));

    }

    /**
     * @param \App\Http\Requests\NoticeStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(NoticeStoreRequest $request)
    {

        //We got 20 companies in the database, let's pick a random one
//        $validated = $request->safe()->merge(['user_id' => auth()->id()]);
        $random = rand(1,20);
        $user = User::firstWhere('id','=',$random);
        $validated = $request->safe()->merge(
            [
                'user_id' => $random,
                'title' => "Claim #". rand(1000,200000) . ' - ' . $user->name

            ]);

        $notice = Notice::create($validated->toArray());

        return response()->json([
            'status' => true,
            'message' => "Notice Created successfully!",
            'notice' => $notice
        ], 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\NoticeStoreRequest;
use App\Models\Notice;
use Illuminate\Http\Request;

class NoticeController extends Controller
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
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Notice $notice)
    {
        return view('notice.show', compact('notice'));
    }

    /**
     * @param \App\Http\Requests\NoticeStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(NoticeStoreRequest $request)
    {
        $notice = Notice::create($request->validated());

        return redirect()->route('notice.index');
    }
}

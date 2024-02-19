<?php

namespace App\Http\Controllers;

use App\Services\LogMessageQueryService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Yoeriboven\LaravelLogDb\Models\LogMessage;

class LogMessagesController extends Controller
{
    public function __construct(protected LogMessageQueryService $log_message_query_service)
    {

    }


    /**
     * Display a listing of the resource.
     *
     *
     * @return Application|Factory|View
     */
    public function index(Request $request): Factory|View|Application
    {
        $log_messages = $this->log_message_query_service->query($request->query())->orderBy('id', 'desc')->paginate(10);

        return view('log_messages.index', [
            'log_messages' => $log_messages
        ]);
    }


    /**
     * Remove the specified resource from storage.
     *
     *
     * @return RedirectResponse
     */
    public function destroy(): RedirectResponse
    {
        LogMessage::truncate();
        return redirect()->route('log-messages.index')->with('success', 'The log messages have been truncated');
    }
}

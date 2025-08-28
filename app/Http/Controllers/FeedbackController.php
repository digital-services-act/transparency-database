<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeedbackSendRequest;
use App\Mail\FeedbackMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Stevebauman\Purify\Facades\Purify;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        return view('feedback.feedback');
    }

    public function send(FeedbackSendRequest $request)
    {
        $purified_feedback = Purify::clean($request->get('feedback'));

        //        return new FeedbackMail($purified_feedback);
        Mail::to(config('dsa.FEEDBACK_MAIL'))
            ->send(new FeedbackMail($purified_feedback));

        return redirect()->route('feedback.index')->with('success', 'Your feedback has been successfully sent.');
    }
}

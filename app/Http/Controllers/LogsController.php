<?php

namespace App\Http\Controllers;

use Spatie\Activitylog\Models\Activity;

class LogsController extends Controller
{
    public function index()
    {
        $logs = Activity::orderBy('id', 'desc')->paginate(50);
        return view('logs.index', [
            'logs' => $logs
        ]);
    }
}
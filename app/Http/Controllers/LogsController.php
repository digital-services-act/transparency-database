<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Spatie\Activitylog\Models\Activity;

class LogsController extends Controller
{
    public function index(): Factory|View|Application
    {
        $logs = Activity::orderBy('id', 'desc')->paginate(50);
        return view('logs.index', [
            'logs' => $logs
        ]);
    }
}
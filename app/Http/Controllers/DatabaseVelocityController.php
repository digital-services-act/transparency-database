<?php

namespace App\Http\Controllers;

use App\Models\DatabaseVelocity;
use Illuminate\Contracts\View\View;

class DatabaseVelocityController extends Controller
{
    public function index(): View
    {
        $velocities = DatabaseVelocity::query()
            ->orderByDesc('created_at')
            ->limit(60)
            ->get()
            ->reverse()
            ->values();

        return view('admin.database-velocity', [
            'velocities' => $velocities,
        ]);
    }
}

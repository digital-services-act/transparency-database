<?php

namespace App\Http\Controllers;

use App\Services\DayArchiveService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class DayArchiveController extends Controller
{
    protected DayArchiveService $day_archive_service;

    public function __construct(DayArchiveService $day_archive_service)
    {
        $this->day_archive_service = $day_archive_service;
    }

    public function index(Request $request)
    {
        $dayarchives = $this->day_archive_service->masterList()->paginate(50);

        return view('dayarchive.index', compact([
            'dayarchives',
        ]));
    }

    public function download(string $date)
    {
        dd("here");
        try {
            $date = Carbon::createFromFormat('Y-m-d', $date);
            $dayarchive = $this->day_archive_service->getDayArchiveByDate($date);
            if ($dayarchive && $dayarchive->completed_at) {
                return redirect($dayarchive->url);
            }
        } catch (\Exception $e) {
            Log::error('Error retrieving day archive full: ' . $e->getMessage());
        }
        abort(Response::HTTP_NOT_FOUND);
    }

    public function downloadLight(string $date)
    {
        try {
            $date = Carbon::createFromFormat('Y-m-d', $date);
            $dayarchive = $this->day_archive_service->getDayArchiveByDate($date);
            if ($dayarchive && $dayarchive->completed_at) {
                return redirect($dayarchive->urllight);
            }
        } catch (\Exception $e) {
            Log::error('Error retrieving day archive light: ' . $e->getMessage());
        }
        abort(Response::HTTP_NOT_FOUND);
    }
}
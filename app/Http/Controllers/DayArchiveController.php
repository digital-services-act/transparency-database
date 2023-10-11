<?php

namespace App\Http\Controllers;

use App\Services\DayArchiveService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
        $dayarchive = $this->day_archive_service->getDayArchiveByDate($date);
        if ($dayarchive && $dayarchive->completed_at) {
            return redirect($dayarchive->url);
        }
        abort(Response::HTTP_NOT_FOUND);
    }

    public function downloadLight(string $date)
    {
        $dayarchive = $this->day_archive_service->getDayArchiveByDate($date);
        if ($dayarchive && $dayarchive->completed_at) {
            return redirect($dayarchive->urllight);
        }
        abort(Response::HTTP_NOT_FOUND);
    }
}
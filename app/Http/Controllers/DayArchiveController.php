<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use App\Services\DayArchiveQueryService;
use App\Services\DayArchiveService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

class DayArchiveController extends Controller
{
    protected DayArchiveService $day_archive_service;

    protected DayArchiveQueryService $day_archive_query_service;

    public function __construct(
        DayArchiveService $day_archive_service,
        DayArchiveQueryService $day_archive_query_service
    )
    {
        $this->day_archive_service = $day_archive_service;
        $this->day_archive_query_service = $day_archive_query_service;
    }

    public function index(Request $request): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {

        $dayarchives = $this->day_archive_query_service->query($request->query());

        $dayarchives = $dayarchives->orderBy('date', 'DESC')->paginate(50)->withQueryString()->appends('query');

        $platform = false;
        $uuid = trim($request->get('uuid'));
        if ($uuid) {
            /** @var Platform $platform */
            $platform = Platform::query()->where('uuid', $uuid)->first();
        }

        $options = $this->prepareOptions();

        return view('dayarchive.index', compact([
            'dayarchives',
            'options',
            'platform'
        ]));
    }

    private function prepareOptions(): array
    {
        $platforms = Platform::Vlops()->orderBy('name')->get()->map(function ($platform) {
            return [
                'value' => $platform->uuid,
                'label' => $platform->name
            ];
        })->toArray();

        array_unshift($platforms, [
            'value' => ' ',
            'label' => 'All Platforms'
        ]);

        return compact(
            'platforms'
        );
    }
}

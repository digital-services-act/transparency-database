<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use App\Services\DayArchiveService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
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

    public function index(Request $request, string $uuid = ''): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        $platform = false;
        if ($uuid) {
            /** @var Platform $platform */
            $platform = Platform::query()->where('uuid', $uuid)->first();
        }

        if ($platform) {
            $dayarchives = $this->day_archive_service->platformList($platform)->paginate(50);
        } else {
            $dayarchives = $this->day_archive_service->globalList()->paginate(50);
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
            'value' => '',
            'label' => 'All Platforms'
        ]);

        return compact(
            'platforms'
        );
    }
}
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
    public function __construct(protected DayArchiveService $day_archive_service, protected DayArchiveQueryService $day_archive_query_service)
    {
    }

    public function index(Request $request): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        $dayarchives = $this->day_archive_query_service->query($request->query());

        $dayarchives = $dayarchives->orderBy('date', 'DESC')->paginate(50)->withQueryString()->appends('query');

        $platform = false;
        $uuid     = trim((string) $request->get('uuid'));
        if ($uuid !== '' && $uuid !== '0') {
            /** @var Platform $platform */
            $platform = Platform::query()->where('uuid', $uuid)->first();
        }

        $options = $this->prepareOptions();

        return view('dayarchive.index', ['dayarchives' => $dayarchives, 'options' => $options, 'platform' => $platform]);
    }

    private function prepareOptions(): array
    {
        $platforms = Platform::Vlops()->orderBy('name')->get()->map(fn($platform) => [
            'value' => $platform->uuid,
            'label' => $platform->name
        ])->toArray();

        array_unshift($platforms, [
            'value' => ' ',
            'label' => 'All Platforms'
        ]);

        return ['platforms' => $platforms];
    }
}

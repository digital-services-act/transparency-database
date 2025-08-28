<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use App\Services\DayArchiveQueryService;
use App\Services\DayArchiveService;
use App\Services\PlatformQueryService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DataDownloadController extends Controller
{
    public function __construct(protected DayArchiveService $day_archive_service, protected DayArchiveQueryService $day_archive_query_service, protected PlatformQueryService $platform_query_service) {}

    public function index(Request $request): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        $dayarchives = $this->day_archive_query_service->query($request->query());
        $dayarchives = $dayarchives->orderBy('date', 'DESC')->paginate(50)->withQueryString()->appends('query');

        $reindexing = Cache::get('reindexing', false);
        $platform = false;
        $uuid = trim((string) $request->get('uuid'));
        if ($uuid !== '' && $uuid !== '0') {
            /** @var Platform $platform */
            $platform = Platform::query()->where('uuid', $uuid)->first();
        }

        $options = $this->prepareOptions();

        return view('explore-data.download', [
            'dayarchives' => $dayarchives,
            'options' => $options,
            'platform' => $platform,
            'reindexing' => $reindexing,
        ]);
    }

    private function prepareOptions(): array
    {
        $platforms = $this->platform_query_service->getPlatformDropDownOptions();

        array_unshift($platforms, [
            'value' => ' ',
            'label' => 'All Platforms',
        ]);

        return ['platforms' => $platforms];
    }
}

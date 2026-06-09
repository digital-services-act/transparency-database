<?php

namespace App\Http\Controllers;

use App\Models\DayArchive;
use App\Models\Platform;
use App\Services\DayArchiveQueryService;
use App\Services\DayArchiveService;
use App\Services\PlatformQueryService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class DataDownloadController extends Controller
{
    public function __construct(protected DayArchiveService $day_archive_service, protected DayArchiveQueryService $day_archive_query_service, protected PlatformQueryService $platform_query_service) {}

    public function index(Request $request): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        $validated = $request->validate([
            'platform_id' => 'nullable|integer|exists:platforms,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
        ]);

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

    public function download(DayArchive $dayArchive, string $type): RedirectResponse
    {
<<<<<<< HEAD
=======
        return $this->redirectToArchiveUrl($dayArchive, $type);
    }

    public function archiveByFilename(string $platformSlug, string $date, string $version): RedirectResponse
    {
        $dayArchive = $this->findCompletedArchive($platformSlug, $date);

        return $this->redirectToArchiveUrl($dayArchive, $version);
    }

    public function archiveChecksumByFilename(string $platformSlug, string $date, string $version): RedirectResponse
    {
        $dayArchive = $this->findCompletedArchive($platformSlug, $date);
        $type = $version === 'full' ? 'sha1' : 'sha1light';

        return $this->redirectToArchiveUrl($dayArchive, $type);
    }

    private function findCompletedArchive(string $platformSlug, string $date): DayArchive
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            abort(404, 'Invalid date format');
        }

        $query = DayArchive::query()
            ->whereDate('date', $date)
            ->whereNotNull('completed_at');

        if ($platformSlug === 'global') {
            $dayArchive = $query->whereNull('platform_id')->first();
        } else {
            $platform = Platform::query()
                ->get(['id', 'name'])
                ->first(static fn (Platform $platform): bool => $platform->slugifyName() === $platformSlug);

            $dayArchive = $platform
                ? $query->where('platform_id', $platform->id)->first()
                : null;
        }

        if (! $dayArchive) {
            abort(404, 'Archive not found');
        }

        return $dayArchive;
    }

    private function redirectToArchiveUrl(DayArchive $dayArchive, string $type): RedirectResponse
    {
>>>>>>> dev
        $urlMap = [
            'full' => $dayArchive->url,
            'light' => $dayArchive->urllight,
            'sha1' => $dayArchive->sha1url,
            'sha1light' => $dayArchive->sha1urllight,
        ];

        if (! isset($urlMap[$type])) {
            abort(404, 'Invalid download type');
        }

        $storedUrl = $urlMap[$type];

        if (empty($storedUrl)) {
            abort(404, 'File not found');
        }

        // amazonaws
        // if the url is an older s3 url, we can redirect directly to it
        if (str_contains($storedUrl, 'amazonaws')) {
            return redirect()->away($storedUrl);
        }

        // Extract filename from stored URL
        $filename = basename(parse_url($storedUrl, PHP_URL_PATH));

        // Generate presigned URL valid for 60 minutes (sufficient for large file downloads)
        $disk = Storage::disk('s3ds');
        $presignedUrl = $disk->temporaryUrl($filename, now()->addMinutes(60));

        return redirect()->away($presignedUrl);
    }

    public function aggregates(string $date, string $ext): RedirectResponse
    {
        // examples
        // "aggregates-2026-06-01.csv",
        // "aggregates-2026-06-01.json",

        $allowedExts = ['csv', 'json'];
        if (! in_array($ext, $allowedExts)) {
            abort(404, 'Invalid file extension');
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            abort(404, 'Invalid date format');
        }

        $filename = "aggregates-{$date}.{$ext}";
        $disk = Storage::disk('s3ds');
        
        if (! $disk->exists($filename)) {
            abort(404, 'File not found');
        }

        $presignedUrl = $disk->temporaryUrl($filename, now()->addMinutes(60));

        return redirect()->away($presignedUrl);
    }
}

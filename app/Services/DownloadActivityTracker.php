<?php

namespace App\Services;

use App\Models\DayArchive;
use App\Models\DownloadEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class DownloadActivityTracker
{
    private const SESSION_TRACKING_KEY = 'download_activity_id';

    public function trackArchive(Request $request, DayArchive $dayArchive, string $type, string $filename): void
    {
        $this->record([
            'day_archive_id' => $dayArchive->id,
            'platform_id' => $dayArchive->platform_id,
            'archive_date' => $dayArchive->date,
            'download_kind' => in_array($type, ['sha1', 'sha1light'], true) ? 'checksum' : 'archive',
            'file_type' => $type,
            'filename' => $filename,
            'route_name' => $request->route()?->getName(),
            'session_hash' => $this->sessionHash($request),
        ]);
    }

    public function trackAggregate(Request $request, string $date, string $extension, string $filename): void
    {
        $this->record([
            'archive_date' => $date,
            'download_kind' => 'aggregate',
            'file_type' => $extension,
            'filename' => $filename,
            'route_name' => $request->route()?->getName(),
            'session_hash' => $this->sessionHash($request),
        ]);
    }

    private function sessionHash(Request $request): ?string
    {
        if (! $request->hasSession()) {
            return null;
        }

        $session = $request->session();
        $trackingId = $session->get(self::SESSION_TRACKING_KEY);

        if (! is_string($trackingId) || $trackingId === '') {
            $trackingId = Str::random(40);
            $session->put(self::SESSION_TRACKING_KEY, $trackingId);
        }

        return hash_hmac('sha256', $trackingId, (string) config('app.key'));
    }

    private function record(array $attributes): void
    {
        try {
            DownloadEvent::query()->create($attributes);
        } catch (Throwable $throwable) {
            Log::warning('Failed to record download activity.', [
                'exception' => $throwable::class,
                'message' => $throwable->getMessage(),
            ]);
        }
    }
}

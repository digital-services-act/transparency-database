<?php

namespace Database\Seeders;

use App\Models\DownloadEvent;
use App\Models\Platform;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use RuntimeException;

class DownloadEventSeeder extends Seeder
{
    private const DAYS = 45;

    private const SESSION_COUNT = 24;

    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('DownloadEventSeeder cannot run in production.');
        }

        DownloadEvent::query()
            ->where('filename', 'like', 'demo-%')
            ->delete();

        $platformIds = Platform::query()->pluck('id')->all();
        $sessions = array_map(
            static fn (int $number): string => hash('sha256', "demo-download-session-{$number}"),
            range(1, self::SESSION_COUNT)
        );

        for ($daysAgo = 0; $daysAgo < self::DAYS; $daysAgo++) {
            $date = now()->startOfDay()->subDays($daysAgo);
            $dailyRequests = max(2, 10 - intdiv($daysAgo, 7)) + ($daysAgo % 3);

            for ($request = 0; $request < $dailyRequests; $request++) {
                $kindIndex = ($daysAgo + $request) % 10;
                $sessionHash = $sessions[($daysAgo * 3 + $request) % self::SESSION_COUNT];
                $createdAt = $this->createdAt($date, $daysAgo, $request, $dailyRequests);

                if ($kindIndex < 7) {
                    $this->createArchive($daysAgo, $request, $createdAt, $sessionHash, $platformIds);
                } elseif ($kindIndex < 9) {
                    $this->createAggregate($daysAgo, $request, $createdAt, $sessionHash);
                } else {
                    $this->createChecksum($daysAgo, $request, $createdAt, $sessionHash, $platformIds);
                }
            }
        }
    }

    private function createdAt(Carbon $date, int $daysAgo, int $request, int $dailyRequests): Carbon
    {
        if ($daysAgo === 0) {
            $elapsedMinutes = max(1, (int) $date->diffInMinutes(now()));
            $minuteOffset = intdiv($elapsedMinutes * ($request + 1), $dailyRequests + 1);

            return $date->copy()->addMinutes($minuteOffset);
        }

        return $date->copy()
            ->addHours(7 + (($request * 2) % 15))
            ->addMinutes(($daysAgo * 7 + $request * 11) % 60);
    }

    private function createArchive(
        int $daysAgo,
        int $request,
        Carbon $createdAt,
        string $sessionHash,
        array $platformIds
    ): void {
        $type = $request % 4 === 0 ? 'light' : 'full';
        $routeName = $request % 3 === 0
            ? 'dayarchive.download.filename'
            : 'dayarchive.download';
        $archiveDate = $createdAt->copy()->subDays(1 + (($daysAgo + $request) % 20))->toDateString();

        DownloadEvent::factory()->archive()->forSession($sessionHash)->create([
            'platform_id' => $this->platformId($platformIds, $daysAgo + $request),
            'archive_date' => $archiveDate,
            'file_type' => $type,
            'filename' => "demo-sor-global-{$archiveDate}-{$type}.zip",
            'route_name' => $routeName,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    private function createChecksum(
        int $daysAgo,
        int $request,
        Carbon $createdAt,
        string $sessionHash,
        array $platformIds
    ): void {
        $type = $request % 2 === 0 ? 'sha1' : 'sha1light';
        $routeName = $request % 2 === 0
            ? 'dayarchive.download.filename.sha1'
            : 'dayarchive.download';
        $archiveDate = $createdAt->copy()->subDays(1 + (($daysAgo + $request) % 20))->toDateString();

        DownloadEvent::factory()->checksum()->forSession($sessionHash)->create([
            'platform_id' => $this->platformId($platformIds, $daysAgo + $request),
            'archive_date' => $archiveDate,
            'file_type' => $type,
            'filename' => "demo-sor-global-{$archiveDate}.zip.sha1",
            'route_name' => $routeName,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    private function createAggregate(
        int $daysAgo,
        int $request,
        Carbon $createdAt,
        string $sessionHash
    ): void {
        $type = $request % 2 === 0 ? 'csv' : 'json';
        $archiveDate = $createdAt->copy()->subDays(1 + (($daysAgo + $request) % 20))->toDateString();

        DownloadEvent::factory()->aggregate()->forSession($sessionHash)->create([
            'archive_date' => $archiveDate,
            'file_type' => $type,
            'filename' => "demo-aggregates-{$archiveDate}.{$type}",
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    private function platformId(array $platformIds, int $index): ?int
    {
        if ($platformIds === [] || $index % 4 === 0) {
            return null;
        }

        return $platformIds[$index % count($platformIds)];
    }
}

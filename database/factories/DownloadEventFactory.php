<?php

namespace Database\Factories;

use App\Models\DownloadEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DownloadEvent>
 */
class DownloadEventFactory extends Factory
{
    protected $model = DownloadEvent::class;

    public function definition(): array
    {
        return $this->archiveAttributes();
    }

    public function archive(): static
    {
        return $this->state(fn (): array => $this->archiveAttributes());
    }

    public function checksum(): static
    {
        return $this->state(function (): array {
            $version = $this->faker->randomElement(['full', 'light']);
            $type = $version === 'full' ? 'sha1' : 'sha1light';

            return [
                'download_kind' => 'checksum',
                'file_type' => $type,
                'filename' => "sor-global-{$this->faker->date()}.zip.sha1",
                'route_name' => $this->faker->randomElement([
                    'dayarchive.download',
                    'dayarchive.download.filename.sha1',
                ]),
            ];
        });
    }

    public function aggregate(): static
    {
        return $this->state(function (): array {
            $date = $this->faker->date();
            $type = $this->faker->randomElement(['csv', 'json']);

            return [
                'day_archive_id' => null,
                'platform_id' => null,
                'archive_date' => $date,
                'download_kind' => 'aggregate',
                'file_type' => $type,
                'filename' => "aggregates-{$date}.{$type}",
                'route_name' => 'aggregates.download',
            ];
        });
    }

    public function forSession(string $sessionHash): static
    {
        return $this->state(fn (): array => [
            'session_hash' => $sessionHash,
        ]);
    }

    private function archiveAttributes(): array
    {
        $date = $this->faker->date();
        $type = $this->faker->randomElement(['full', 'light']);

        return [
            'day_archive_id' => null,
            'platform_id' => null,
            'archive_date' => $date,
            'download_kind' => 'archive',
            'file_type' => $type,
            'filename' => "sor-global-{$date}-{$type}.zip",
            'route_name' => $this->faker->randomElement([
                'dayarchive.download',
                'dayarchive.download.filename',
            ]),
            'session_hash' => hash('sha256', $this->faker->uuid()),
        ];
    }
}

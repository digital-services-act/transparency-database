<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DayArchiveWorkspace
{
    public function __construct(private readonly ?string $bucket_data_path = null) {}

    public function path(string $path = ''): string
    {
        $base_path = rtrim($this->basePath(), DIRECTORY_SEPARATOR);

        if ($path === '') {
            return $base_path.DIRECTORY_SEPARATOR;
        }

        return $base_path.DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR);
    }

    public function glob(string $pattern): array
    {
        return File::glob($this->path($pattern)) ?: [];
    }

    public function deleteFilesForDate(string $date): void
    {
        File::delete($this->glob('*'.$date.'*'));
    }

    public function put(string $path, string $contents): void
    {
        File::put($this->path($path), $contents);
    }

    private function basePath(): string
    {
        $bucket_data_path = $this->bucket_data_path ?? base_path('bucket-data');

        if (File::isDirectory($bucket_data_path)) {
            return $bucket_data_path;
        }

        return Storage::path('');
    }
}

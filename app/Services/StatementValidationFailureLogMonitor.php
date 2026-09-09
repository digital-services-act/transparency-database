<?php

namespace App\Services;

class StatementValidationFailureLogMonitor
{
    private const string SINGLE_MESSAGE = 'Statement Store Request Validation Failure';

    private const string MULTIPLE_MESSAGE = 'Statement Multiple Store Request Validation Failure';

    /**
     * @var array<string, array{attempts: int, mistake_count: int, mistakes: array<string, int>}>
     */
    private array $platforms = [];

    /**
     * @var array{single: int, multiple: int}
     */
    private array $endpointCounts = [
        'single' => 0,
        'multiple' => 0,
    ];

    private int $failureCount = 0;

    private int $mistakeCount = 0;

    public function ingest(string $line): bool
    {
        $failure = $this->parseLine($line);

        if ($failure === null) {
            return false;
        }

        $platform = $failure['platform'];

        if (! isset($this->platforms[$platform])) {
            $this->platforms[$platform] = [
                'attempts' => 0,
                'mistake_count' => 0,
                'mistakes' => [],
            ];
        }

        $this->failureCount++;
        $this->endpointCounts[$failure['endpoint']]++;
        $this->platforms[$platform]['attempts']++;

        foreach ($failure['mistakes'] as $mistake) {
            $this->mistakeCount++;
            $this->platforms[$platform]['mistake_count']++;
            $this->platforms[$platform]['mistakes'][$mistake] = ($this->platforms[$platform]['mistakes'][$mistake] ?? 0) + 1;
        }

        return true;
    }

    /**
     * @return array{
     *     failures: int,
     *     mistakes: int,
     *     endpoints: array{single: int, multiple: int},
     *     platforms: array<int, array{platform: string, attempts: int, mistake_count: int, mistakes: array<string, int>}>
     * }
     */
    public function summary(): array
    {
        return [
            'failures' => $this->failureCount,
            'mistakes' => $this->mistakeCount,
            'endpoints' => $this->endpointCounts,
            'platforms' => $this->topPlatforms(PHP_INT_MAX),
        ];
    }

    /**
     * @return array<int, array{platform: string, attempts: int, mistake_count: int, mistakes: array<string, int>}>
     */
    public function topPlatforms(int $limit = 5): array
    {
        $platforms = [];

        foreach ($this->platforms as $platform => $stats) {
            $platforms[] = [
                'platform' => $platform,
                'attempts' => $stats['attempts'],
                'mistake_count' => $stats['mistake_count'],
                'mistakes' => $this->sortMistakes($stats['mistakes']),
            ];
        }

        usort($platforms, function (array $left, array $right): int {
            if ($left['attempts'] !== $right['attempts']) {
                return $right['attempts'] <=> $left['attempts'];
            }

            if ($left['mistake_count'] !== $right['mistake_count']) {
                return $right['mistake_count'] <=> $left['mistake_count'];
            }

            return $left['platform'] <=> $right['platform'];
        });

        return array_slice($platforms, 0, $limit);
    }

    /**
     * @return array{platform: string, endpoint: 'single'|'multiple', mistakes: array<int, string>}|null
     */
    private function parseLine(string $line): ?array
    {
        $line = $this->stripAnsiEscapeSequences($line);
        $endpoint = null;
        $messagePosition = false;

        foreach ([self::MULTIPLE_MESSAGE => 'multiple', self::SINGLE_MESSAGE => 'single'] as $message => $type) {
            $messagePosition = strpos($line, $message);

            if ($messagePosition !== false) {
                $endpoint = $type;
                break;
            }
        }

        if ($endpoint === null || $messagePosition === false) {
            return null;
        }

        $jsonPosition = strpos($line, '{', $messagePosition);

        if ($jsonPosition === false) {
            return null;
        }

        $json = $this->extractJsonObject($line, $jsonPosition);

        if ($json === null) {
            return null;
        }

        $context = json_decode($json, true);

        if (! is_array($context)) {
            return null;
        }

        $platform = $context['platform'] ?? '(unknown platform)';

        if (! is_string($platform) || trim($platform) === '') {
            $platform = '(unknown platform)';
        }

        return [
            'platform' => $platform,
            'endpoint' => $endpoint,
            'mistakes' => $this->extractMistakes($context['errors'] ?? []),
        ];
    }

    private function stripAnsiEscapeSequences(string $line): string
    {
        return preg_replace('/\x1B(?:[@-Z\\\\-_]|\[[0-?]*[ -\/]*[@-~])/', '', $line) ?? $line;
    }

    private function extractJsonObject(string $line, int $jsonPosition): ?string
    {
        $length = strlen($line);
        $depth = 0;
        $inString = false;
        $escaped = false;

        for ($position = $jsonPosition; $position < $length; $position++) {
            $character = $line[$position];

            if ($inString) {
                if ($escaped) {
                    $escaped = false;
                } elseif ($character === '\\') {
                    $escaped = true;
                } elseif ($character === '"') {
                    $inString = false;
                }

                continue;
            }

            if ($character === '"') {
                $inString = true;

                continue;
            }

            if ($character === '{') {
                $depth++;
            } elseif ($character === '}') {
                $depth--;

                if ($depth === 0) {
                    return substr($line, $jsonPosition, $position - $jsonPosition + 1);
                }
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function extractMistakes(mixed $errors, array $path = []): array
    {
        if (is_string($errors)) {
            return [$this->formatMistake($path, $errors)];
        }

        if (! is_array($errors)) {
            return [];
        }

        if ($this->isStringList($errors)) {
            return array_map(
                fn (string $message): string => $this->formatMistake($path, $message),
                $errors
            );
        }

        $mistakes = [];

        foreach ($errors as $key => $value) {
            $nextPath = $path;
            $segment = (string) $key;

            if (! $this->shouldSkipPathSegment($segment)) {
                $nextPath[] = $segment;
            }

            array_push($mistakes, ...$this->extractMistakes($value, $nextPath));
        }

        return $mistakes;
    }

    /**
     * @param  array<mixed>  $value
     */
    private function isStringList(array $value): bool
    {
        if ($value === []) {
            return false;
        }

        if (! array_is_list($value)) {
            return false;
        }

        foreach ($value as $item) {
            if (! is_string($item)) {
                return false;
            }
        }

        return true;
    }

    private function shouldSkipPathSegment(string $segment): bool
    {
        return $segment === 'Illuminate\\Support\\MessageBag'
            || preg_match('/^statement_\d+$/', $segment) === 1;
    }

    private function formatMistake(array $path, string $message): string
    {
        $message = trim($message);
        $field = implode('.', array_filter($path, fn (string $segment): bool => $segment !== ''));

        if ($field === '') {
            return $message;
        }

        return "{$field}: {$message}";
    }

    /**
     * @param  array<string, int>  $mistakes
     * @return array<string, int>
     */
    private function sortMistakes(array $mistakes): array
    {
        uksort($mistakes, function (string $left, string $right) use ($mistakes): int {
            if ($mistakes[$left] !== $mistakes[$right]) {
                return $mistakes[$right] <=> $mistakes[$left];
            }

            return $left <=> $right;
        });

        return $mistakes;
    }
}

<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use RuntimeException;

trait CommandTrait
{
    public function sanitizeDateArgument(string $name = ''): Carbon
    {
        $date = $this->argument($name ?: 'date');

        if ($date === 'yesterday') {
            $date = Carbon::yesterday();
        } elseif ($date === 'today') {
            $date = Carbon::today();
        } elseif (preg_match('/^\d+$/', (string) $date)) {
            $date = Carbon::now()->subDays((int) $date);
        } else {
            try {
                $date = Carbon::createFromFormat('Y-m-d', $date);
            } catch (Exception) {
                $this->error('Issue with the date provided, checked the format yyyy-mm-dd');
                throw new RuntimeException('Issue with the date provided, checked the format yyyy-mm-dd');
            }
        }

        return $date->startOfDay();
    }

    public function intifyArgument(string $argument): int
    {
        return (int) $this->argument($argument);
    }

    public function boolifyArgument(string $argument): bool
    {
        return $this->argument($argument) === 'true';
    }

    public function positiveIntOption(string $name): int
    {
        $value = (int) $this->option($name);

        if ($value < 1) {
            throw new InvalidArgumentException(sprintf('The --%s option must be greater than zero.', $name));
        }

        return $value;
    }

    public function nullablePositiveIntOption(string $name): ?int
    {
        $raw = $this->option($name);

        if ($raw === null || $raw === '') {
            return null;
        }

        $value = (int) $raw;

        if ($value < 1) {
            throw new InvalidArgumentException(sprintf('The --%s option must be greater than zero.', $name));
        }

        return $value;
    }

    public function nonNegativeIntOption(string $name): int
    {
        $value = (int) $this->option($name);

        if ($value < 0) {
            throw new InvalidArgumentException(sprintf('The --%s option must be zero or greater.', $name));
        }

        return $value;
    }
}

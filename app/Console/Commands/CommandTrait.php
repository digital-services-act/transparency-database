<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Support\Carbon;
use RuntimeException;

trait CommandTrait
{
    public function sanitizeDateArgument(): Carbon
    {
        $date = $this->argument('date');

        if ($date === 'yesterday') {
            $date = Carbon::yesterday();
        } elseif ($date === 'today') {
            $date = Carbon::today();
        } elseif (preg_match('/^\d+$/', (string) $date)) {
            $date = Carbon::now()->subDays((int)$date);
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
        return (int)$this->argument($argument);
    }

    public function boolifyArgument(string $argument): bool
    {
        return $this->argument($argument) === 'true';
    }
}

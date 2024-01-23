<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Support\Carbon;
use RuntimeException;

use function PHPUnit\Framework\throwException;

trait CommandTrait
{
    public function sanitizeDateArgument(): Carbon
    {
        $date = $this->argument('date');
        if ($date === 'yesterday') {
            $date = Carbon::yesterday();
        } elseif ($date === 'today') {
            $date = Carbon::today();
        } else {
            try {
                $date = Carbon::createFromFormat('Y-m-d', $date);
            } catch (Exception $e) {
                $this->error('Issue with the date provided, checked the format yyyy-mm-dd');
                throwException(new RuntimeException('Issue with the date provided, checked the format yyyy-mm-dd'));
            }
        }
        return $date;
    }

    public function intifyArgument(string $argument): int
    {
        return (int)$this->argument($argument);
    }

    public function boolifyArgument(string $argument): bool
    {
        return (bool)$this->argument($argument);
    }

}
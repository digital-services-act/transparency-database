<?php

namespace App\Services;

use Illuminate\Console\Command;
use RuntimeException;

class CommandValidationService
{
    public function validateRequiredArgument(Command $command, string $argument): string
    {
        $value = $command->argument($argument);
        if (empty($value)) {
            throw new RuntimeException("The '{$argument}' argument is required.");
        }
        return $value;
    }

    public function validateBooleanArgument(Command $command, string $argument): bool
    {
        $value = strtolower($command->argument($argument));
        if (!in_array($value, ['true', 'false', '1', '0'], true)) {
            throw new RuntimeException("The '{$argument}' must be a boolean value (true/false).");
        }
        return in_array($value, ['true', '1'], true);
    }

    public function validateNumericArgument(Command $command, string $argument, int $min = null, int $max = null): int
    {
        $value = $command->argument($argument);
        if (!is_numeric($value)) {
            throw new RuntimeException("The '{$argument}' must be a numeric value.");
        }
        $value = (int) $value;
        
        if ($min !== null && $value < $min) {
            throw new RuntimeException("The '{$argument}' must be at least {$min}.");
        }
        
        if ($max !== null && $value > $max) {
            throw new RuntimeException("The '{$argument}' must not exceed {$max}.");
        }
        
        return $value;
    }
}

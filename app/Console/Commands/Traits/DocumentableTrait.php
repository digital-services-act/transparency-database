<?php

namespace App\Console\Commands\Traits;

trait DocumentableTrait
{
    /**
     * Get the command description with examples.
     */
    public function getDetailedDescription(): string
    {
        $description = $this->description."\n\n";
        $description .= "Usage:\n";
        $description .= '  '.$this->signature."\n\n";

        if (property_exists($this, 'arguments') && ! empty($this->arguments)) {
            $description .= "Arguments:\n";
            foreach ($this->arguments as $argument => $desc) {
                $description .= "  {$argument}: {$desc}\n";
            }
            $description .= "\n";
        }

        if (property_exists($this, 'examples') && ! empty($this->examples)) {
            $description .= "Examples:\n";
            foreach ($this->examples as $example) {
                $description .= "  {$example}\n";
            }
        }

        return $description;
    }
}

<?php

namespace Tests\Feature\Console\Commands\Traits;

use App\Console\Commands\Traits\DocumentableTrait;
use Illuminate\Console\Command;
use Tests\TestCase as TraitTestCase;

class DocumentableTraitTest extends TraitTestCase
{
    private Command $command;

    protected function setUp(): void
    {
        $this->createApplication();

        // Create a test command that uses the DocumentableTrait
        $this->command = new class extends Command
        {
            use DocumentableTrait;

            protected $signature = 'test:command {arg1} {arg2}';

            protected $description = 'Test command description';

            protected $arguments = [
                'arg1' => 'First argument description',
                'arg2' => 'Second argument description',
            ];

            protected $examples = [
                'test:command value1 value2',
                'test:command foo bar',
            ];
        };
    }

    public function test_get_detailed_description_includes_basic_info(): void
    {
        $description = $this->command->getDetailedDescription();

        $this->assertStringContainsString('Test command description', $description);
        $this->assertStringContainsString('Usage:', $description);
        $this->assertStringContainsString('test:command {arg1} {arg2}', $description);
    }

    public function test_get_detailed_description_includes_arguments(): void
    {
        $description = $this->command->getDetailedDescription();

        $this->assertStringContainsString('Arguments:', $description);
        $this->assertStringContainsString('arg1: First argument description', $description);
        $this->assertStringContainsString('arg2: Second argument description', $description);
    }

    public function test_get_detailed_description_includes_examples(): void
    {
        $description = $this->command->getDetailedDescription();

        $this->assertStringContainsString('Examples:', $description);
        $this->assertStringContainsString('test:command value1 value2', $description);
        $this->assertStringContainsString('test:command foo bar', $description);
    }

    public function test_get_detailed_description_without_arguments(): void
    {
        // Create a command without arguments property
        $command = new class extends Command
        {
            use DocumentableTrait;

            protected $signature = 'test:noargs';

            protected $description = 'Test command without arguments';

            protected $examples = ['test:noargs'];
        };

        $description = $command->getDetailedDescription();

        $this->assertStringNotContainsString('Arguments:', $description);
        $this->assertStringContainsString('Examples:', $description);
    }

    public function test_get_detailed_description_without_examples(): void
    {
        // Create a command without examples property
        $command = new class extends Command
        {
            use DocumentableTrait;

            protected $signature = 'test:noexamples';

            protected $description = 'Test command without examples';

            protected $arguments = ['arg' => 'Argument description'];
        };

        $description = $command->getDetailedDescription();

        $this->assertStringContainsString('Arguments:', $description);
        $this->assertStringNotContainsString('Examples:', $description);
    }

    public function test_get_detailed_description_with_empty_arrays(): void
    {
        // Create a command with empty arrays
        $command = new class extends Command
        {
            use DocumentableTrait;

            protected $signature = 'test:empty';

            protected $description = 'Test command with empty arrays';

            protected $arguments = [];

            protected $examples = [];
        };

        $description = $command->getDetailedDescription();

        $this->assertStringNotContainsString('Arguments:', $description);
        $this->assertStringNotContainsString('Examples:', $description);
        $this->assertStringContainsString('Test command with empty arrays', $description);
    }
}

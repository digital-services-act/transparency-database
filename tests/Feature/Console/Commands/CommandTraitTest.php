<?php

namespace Tests\Feature\Console\Commands;

use App\Console\Commands\CommandTrait;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\BufferedOutput;

class TestCommand extends Command
{
    use CommandTrait;

    protected $signature = 'test:command {date} {number} {flag}';

    public function __construct()
    {
        parent::__construct();

        $this->setDefinition(
            new InputDefinition([
                new InputArgument('date', InputArgument::REQUIRED),
                new InputArgument('number', InputArgument::REQUIRED),
                new InputArgument('flag', InputArgument::REQUIRED),
            ])
        );
    }
}

class CommandTraitTest extends TestCase
{
    private TestCommand $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->command = new TestCommand();
    }

    private function setCommandArgument(string $value): void
    {
        $input = new ArrayInput(
            ['date' => $value, 'number' => '0', 'flag' => 'false'],
            $this->command->getDefinition()
        );
        $output = new OutputStyle($input, new BufferedOutput());
        
        $this->command->setInput($input);
        $this->command->setOutput($output);
    }

    /** @test */
    public function it_handles_yesterday_date_argument()
    {
        $this->setCommandArgument('yesterday');
        $result = $this->command->sanitizeDateArgument();
        $this->assertEquals(Carbon::yesterday()->startOfDay(), $result);
    }

    /** @test */
    public function it_handles_today_date_argument()
    {
        $this->setCommandArgument('today');
        $result = $this->command->sanitizeDateArgument();
        $this->assertEquals(Carbon::today()->startOfDay(), $result);
    }

    /** @test */
    public function it_handles_days_ago_date_argument()
    {
        $this->setCommandArgument('5');
        $result = $this->command->sanitizeDateArgument();
        $this->assertEquals(Carbon::now()->subDays(5)->startOfDay(), $result);
    }

    /** @test */
    public function it_handles_specific_date_format()
    {
        $this->setCommandArgument('2023-12-25');
        $result = $this->command->sanitizeDateArgument();
        $this->assertEquals(Carbon::create(2023, 12, 25)->startOfDay(), $result);
    }

    /** @test */
    public function it_throws_exception_for_invalid_date_format()
    {
        $this->setCommandArgument('invalid-date');
        $this->expectException(RuntimeException::class);
        $this->command->sanitizeDateArgument();
    }

    /** @test */
    public function it_converts_argument_to_integer()
    {
        $input = new ArrayInput(
            ['date' => 'today', 'number' => '42', 'flag' => 'false'],
            $this->command->getDefinition()
        );
        $output = new OutputStyle($input, new BufferedOutput());
        
        $this->command->setInput($input);
        $this->command->setOutput($output);
        
        $result = $this->command->intifyArgument('number');
        $this->assertSame(42, $result);
    }

    /** @test */
    public function it_converts_argument_to_boolean()
    {
        $input = new ArrayInput(
            ['date' => 'today', 'number' => '0', 'flag' => 'true'],
            $this->command->getDefinition()
        );
        $output = new OutputStyle($input, new BufferedOutput());
        
        $this->command->setInput($input);
        $this->command->setOutput($output);
        
        $result = $this->command->boolifyArgument('flag');
        $this->assertTrue($result);

        $input = new ArrayInput(
            ['date' => 'today', 'number' => '0', 'flag' => 'false'],
            $this->command->getDefinition()
        );
        $output = new OutputStyle($input, new BufferedOutput());
        
        $this->command->setInput($input);
        $this->command->setOutput($output);
        
        $result = $this->command->boolifyArgument('flag');
        $this->assertFalse($result);
    }
}

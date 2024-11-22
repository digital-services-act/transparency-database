<?php

namespace Tests\Feature\Services;

use App\Services\CommandValidationService;
use Illuminate\Console\Command;
use RuntimeException;
use Tests\TestCase;

class CommandValidationServiceTest extends TestCase
{
    private CommandValidationService $service;
    private Command $command;

    protected function setUp(): void
    {
        // Skip parent::setUp() to avoid database seeding
        $this->createApplication();
        
        $this->service = new CommandValidationService();
        $this->command = $this->createMock(Command::class);
    }

    public function testValidateRequiredArgument(): void
    {
        $this->command->expects($this->once())
            ->method('argument')
            ->with('test')
            ->willReturn('value');

        $result = $this->service->validateRequiredArgument($this->command, 'test');
        $this->assertEquals('value', $result);
    }

    public function testValidateRequiredArgumentThrowsOnEmpty(): void
    {
        $this->command->expects($this->once())
            ->method('argument')
            ->with('test')
            ->willReturn('');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("The 'test' argument is required.");
        
        $this->service->validateRequiredArgument($this->command, 'test');
    }

    public function testValidateBooleanArgument(): void
    {
        $this->command->expects($this->exactly(4))
            ->method('argument')
            ->with('test')
            ->willReturnOnConsecutiveCalls('true', 'false', '1', '0');

        $this->assertTrue($this->service->validateBooleanArgument($this->command, 'test'));
        $this->assertFalse($this->service->validateBooleanArgument($this->command, 'test'));
        $this->assertTrue($this->service->validateBooleanArgument($this->command, 'test'));
        $this->assertFalse($this->service->validateBooleanArgument($this->command, 'test'));
    }

    public function testValidateBooleanArgumentThrowsOnInvalid(): void
    {
        $this->command->expects($this->once())
            ->method('argument')
            ->with('test')
            ->willReturn('invalid');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("The 'test' must be a boolean value (true/false).");
        
        $this->service->validateBooleanArgument($this->command, 'test');
    }

    public function testValidateNumericArgument(): void
    {
        $this->command->expects($this->once())
            ->method('argument')
            ->with('test')
            ->willReturn('42');

        $result = $this->service->validateNumericArgument($this->command, 'test', 0, 100);
        $this->assertEquals(42, $result);
    }

    public function testValidateNumericArgumentThrowsOnNonNumeric(): void
    {
        $this->command->expects($this->once())
            ->method('argument')
            ->with('test')
            ->willReturn('not-a-number');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("The 'test' must be a numeric value.");
        
        $this->service->validateNumericArgument($this->command, 'test');
    }

    public function testValidateNumericArgumentThrowsOnBelowMinimum(): void
    {
        $this->command->expects($this->once())
            ->method('argument')
            ->with('test')
            ->willReturn('5');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("The 'test' must be at least 10.");
        $this->service->validateNumericArgument($this->command, 'test', 10);
    }

    public function testValidateNumericArgumentThrowsOnAboveMaximum(): void
    {
        $this->command->expects($this->once())
            ->method('argument')
            ->with('test')
            ->willReturn('5');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("The 'test' must not exceed 3.");
        $this->service->validateNumericArgument($this->command, 'test', null, 3);
    }
}

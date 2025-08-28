<?php

namespace Tests\Feature\Console\Commands;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;
use Tests\CreatesApplication;

class ToggleCacheVarTest extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear any existing cache before each test
        Cache::flush();
    }

    /** @test */
    public function it_can_set_cache_variable_to_true()
    {
        // Arrange
        $key = 'test_cache_key';

        // Act
        $this->artisan('toggle-cache-var', [
            'key' => $key,
            'state' => 'true',
        ])->assertExitCode(0);

        // Assert
        $this->assertTrue(Cache::get($key));
    }

    /** @test */
    public function it_can_set_cache_variable_to_false()
    {
        // Arrange
        $key = 'test_cache_key';

        // Act
        $this->artisan('toggle-cache-var', [
            'key' => $key,
            'state' => 'false',
        ])->assertExitCode(0);

        // Assert
        $this->assertFalse(Cache::get($key));
    }

    /** @test */
    public function it_can_toggle_existing_cache_variable()
    {
        // Arrange
        $key = 'test_cache_key';
        Cache::forever($key, true);

        // Act & Assert - Toggle to false
        $this->artisan('toggle-cache-var', [
            'key' => $key,
            'state' => 'false',
        ])->assertExitCode(0);
        $this->assertFalse(Cache::get($key));

        // Act & Assert - Toggle back to true
        $this->artisan('toggle-cache-var', [
            'key' => $key,
            'state' => 'true',
        ])->assertExitCode(0);
        $this->assertTrue(Cache::get($key));
    }

    /** @test */
    public function it_handles_empty_key_gracefully()
    {
        // Act
        $this->artisan('toggle-cache-var', [
            'key' => '',
            'state' => 'true',
        ])->assertExitCode(0);

        // Assert - No exception should be thrown
        $this->assertNull(Cache::get(''));
    }

    /** @test */
    public function it_handles_invalid_state_value()
    {
        // Arrange
        $key = 'test_cache_key';

        // Act
        $this->artisan('toggle-cache-var', [
            'key' => $key,
            'state' => 'invalid',
        ])->assertExitCode(0);

        // Assert - Should default to false for invalid state
        $this->assertFalse(Cache::get($key));
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        Cache::flush();
        parent::tearDown();
    }
}

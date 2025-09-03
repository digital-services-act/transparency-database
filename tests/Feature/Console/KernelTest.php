<?php

namespace Tests\Feature\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KernelTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_commands_are_scheduled_when_not_on_task_server(): void
    {
        config()->set('app.is_task_server', false);

        // We need to resolve the kernel and call the schedule method to populate the schedule
        $kernel = $this->app->make(\App\Console\Kernel::class);
        $schedule = new Schedule;
        $this->invokeProtectedMethod($kernel, 'schedule', [$schedule]);

        $events = collect($schedule->events());

        $this->assertCount(0, $events);
    }

    public function test_all_commands_are_scheduled_on_task_server_in_production(): void
    {
        config()->set('app.is_task_server', true);
        config()->set('app.env', 'production');

        // We need to resolve the kernel and call the schedule method to populate the schedule
        $kernel = $this->app->make(\App\Console\Kernel::class);
        $schedule = new Schedule;
        $this->invokeProtectedMethod($kernel, 'schedule', [$schedule]);

        $events = collect($schedule->events());
        $this->assertCount(11, $events);

        // Check for a specific command that is unique to production
        $this->assertTrue($events->contains(function ($event) {
            return str_contains($event->command, 'enrich-home-page-cache --grandtotal') && $event->expression === '0 9 * * *';
        }));
    }

    public function test_all_commands_are_scheduled_on_task_server_in_non_production(): void
    {
        config()->set('app.is_task_server', true);
        config()->set('app.env', 'testing');

        // We need to resolve the kernel and call the schedule method to populate the schedule
        $kernel = $this->app->make(\App\Console\Kernel::class);
        $schedule = new Schedule;
        $this->invokeProtectedMethod($kernel, 'schedule', [$schedule]);

        $events = collect($schedule->events());
        $this->assertCount(11, $events);
    }

    /**
     * Helper to invoke a protected method on an object.
     *
     * @return mixed
     */
    protected function invokeProtectedMethod(object $object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionObject($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}

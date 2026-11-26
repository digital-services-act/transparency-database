<?php

namespace Tests\Feature\Console\Commands;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;
use Tests\CreatesApplication;

class TestTeamsWebhookTest extends BaseTestCase
{
    use CreatesApplication;

    public function test_it_posts_the_hello_world_message_to_the_configured_webhook(): void
    {
        config(['services.teams.daily_webhook' => 'https://example.test/webhook']);
        Http::fake(['example.test/*' => Http::response('', 200)]);

        $this->artisan('teams:webhook-test')
            ->expectsOutput('Hello-world message sent to the daily Teams webhook.')
            ->assertSuccessful();

        Http::assertSent(fn ($request): bool => $request->url() === 'https://example.test/webhook'
            && $request->data() === [
                'type' => 'message',
                'attachments' => [[
                    'contentType' => 'application/vnd.microsoft.card.adaptive',
                    'contentUrl' => null,
                    'content' => [
                        '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
                        'type' => 'AdaptiveCard',
                        'version' => '1.2',
                        'body' => [[
                            'type' => 'TextBlock',
                            'text' => 'Hello world from the transparency database (webhook test).',
                            'wrap' => true,
                        ]],
                    ],
                ]],
            ]
        );
    }

    public function test_it_fails_without_a_configured_webhook(): void
    {
        config(['services.teams.daily_webhook' => null]);
        Http::fake();

        $this->artisan('teams:webhook-test')
            ->expectsOutput('DAILY_TEAMS_WEBHOOK is not configured.')
            ->assertFailed();

        Http::assertNothingSent();
    }

    public function test_it_fails_when_the_webhook_rejects_the_message(): void
    {
        config(['services.teams.daily_webhook' => 'https://example.test/webhook']);
        Http::fake(['example.test/*' => Http::response('', 500)]);

        $this->artisan('teams:webhook-test')
            ->expectsOutput('The daily Teams webhook returned HTTP 500.')
            ->assertFailed();
    }

    public function test_it_fails_when_the_webhook_connection_cannot_be_established(): void
    {
        config(['services.teams.daily_webhook' => 'https://example.test/webhook']);
        Http::fake(['example.test/*' => Http::failedConnection()]);

        $this->artisan('teams:webhook-test')
            ->expectsOutput('Could not connect to the daily Teams webhook.')
            ->assertFailed();
    }
}

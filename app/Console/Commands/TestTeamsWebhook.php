<?php

namespace App\Console\Commands;

use App\Services\TeamsWebhookService;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use RuntimeException;

class TestTeamsWebhook extends Command
{
    protected $signature = 'teams:webhook-test';

    protected $description = 'Send a hello-world message to the daily Microsoft Teams webhook.';

    public function handle(TeamsWebhookService $teamsWebhook): int
    {
        try {
            $response = $teamsWebhook->sendTextCard('Hello world from the transparency database (webhook test).');
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        } catch (ConnectionException) {
            $this->error('Could not connect to the daily Teams webhook.');

            return self::FAILURE;
        }

        if (! $response->successful()) {
            $this->error("The daily Teams webhook returned HTTP {$response->status()}.");

            return self::FAILURE;
        }

        $this->info('Hello-world message sent to the daily Teams webhook.');

        return self::SUCCESS;
    }
}

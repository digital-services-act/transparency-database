<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TeamsWebhookService
{
    public function sendTextCard(string $text): Response
    {
        $webhookUrl = config('services.teams.daily_webhook');

        if (! is_string($webhookUrl) || trim($webhookUrl) === '') {
            throw new RuntimeException('DAILY_TEAMS_WEBHOOK is not configured.');
        }

        return Http::timeout(15)->post($webhookUrl, [
            'type' => 'message',
            'attachments' => [
                [
                    'contentType' => 'application/vnd.microsoft.card.adaptive',
                    'contentUrl' => null,
                    'content' => [
                        '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
                        'type' => 'AdaptiveCard',
                        'version' => '1.2',
                        'body' => [
                            [
                                'type' => 'TextBlock',
                                'text' => $text,
                                'wrap' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}

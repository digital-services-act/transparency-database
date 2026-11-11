<?php

namespace App\Console\Commands;

use App\Models\Platform;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResyncPlatformHasTokens extends Command
{
    protected $signature = 'platforms:resync-has-tokens';

    protected $description = 'Recalculate platform has_tokens flags based on related user tokens.';

    public function handle(): int
    {
        $platformsChecked = 0;
        $platformsUpdated = 0;
        $asOf = now();

        Platform::query()
            ->chunkById(100, function ($platforms) use (&$platformsChecked, &$platformsUpdated, $asOf): void {
                foreach ($platforms as $platform) {
                    $hasTokens = false;

                    if ($platform->users()->whereHas('tokens', static function ($query) use ($asOf): void {
                        $query->where(static function ($expirationQuery) use ($asOf): void {
                            $expirationQuery->whereNull('expires_at')
                                ->orWhere('expires_at', '>=', $asOf);
                        });
                    })->exists()) {
                        $hasTokens = true;
                    }

                    $platformsChecked++;

                    if ((bool) $platform->has_tokens === $hasTokens) {
                        continue;
                    }

                    DB::table('platforms')
                        ->where('id', $platform->id)
                        ->update(['has_tokens' => $hasTokens]);

                    $platformsUpdated++;
                }
            });

        $this->info("Checked {$platformsChecked} platform(s); updated {$platformsUpdated} has_tokens value(s).");

        return self::SUCCESS;
    }
}

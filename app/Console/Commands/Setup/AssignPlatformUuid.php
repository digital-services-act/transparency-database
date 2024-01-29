<?php

namespace App\Console\Commands\Setup;

use App\Models\Platform;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class AssignPlatformUuid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'platform:assign-uuid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Give a uuid to any platform with "-"';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $platforms = Platform::query()->where('uuid', '-')->get();
        foreach ($platforms as $platform)
        {
            $platform->uuid = Str::uuid();
            $platform->save();
        }
    }
}

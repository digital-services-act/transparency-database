<?php

namespace App\Models;


use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    /**
     * The seconds to cache the token for.
     *
     */
    public static int $ttl = 3600;

    /**
     * The interval to refresh the last_used field in database.
     *
     */
    public static int $interval = 3600;

    /**
     * Find the token instance matching the given token.
     *
     * @param string $token
     * @return static|null
     */
    #[\Override]
    public static function findToken($token): ?static
    {
        $id = explode('|', $token)[0];
        $token = Cache::remember(
            'personal-access-token:' . $id,
            config('sanctum.cache.ttl') ?? self::$ttl,
            static fn() => parent::findToken($token) ?? '_null_'
        );
        if ($token === '_null_') {
            return null;
        }

        return $token;
    }

    /**
     * Get the tokenable model that the access token belongs to.
     *
     * @return Attribute
     *
     * help wanted: return type ain't compatible with base class
     *
     * @phpstan-ignore-next-line
     */
    #[\Override]
    public function tokenable(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => Cache::remember(
                sprintf('personal-access-token:%s:tokenable', $attributes['id']),
                config('sanctum.cache.ttl') ?? self::$ttl,
                fn() => parent::tokenable()->first()
            )
        );
    }

    /**
     * Bootstrap the model and its traits.
     *
     * todo update cache
     *
     * @return void
     */
    #[\Override]protected static function boot(): void
    {
        parent::boot();

        static::updating(static function (self $personalAccessToken) {
            $interval = config('sanctum.cache.update_last_used_at_interval') ?? self::$interval;

            try {
                Cache::remember(
                    sprintf('personal-access-token:%s:last_used_at', $personalAccessToken->id),
                    $interval,
                    static function () use ($personalAccessToken) {
                        DB::table($personalAccessToken->getTable())
                          ->where('id', $personalAccessToken->id)
                          ->update($personalAccessToken->getDirty());

                        return now();
                    }
                );
            } catch (Exception $exception) {
                Log::critical('Critical Personal Access Token Error', ['exception' => $exception]);
            }

            return false;
        });

        static::deleting(static function (self $personalAccessToken) {
            Cache::forget('personal-access-token:' . $personalAccessToken->id);
            Cache::forget(sprintf('personal-access-token:%s:last_used_at', $personalAccessToken->id));
            Cache::forget(sprintf('personal-access-token:%s:tokenable', $personalAccessToken->id));
        });
    }

}

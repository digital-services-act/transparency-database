<?php

namespace App\Models;

use Exception;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    use HasFactory;

    /**
     * The seconds to cache the token for.
     */
    public static int $ttl = 300;

    /**
     * The interval to refresh the last_used field in database.
     */
    public static int $interval = 3600;

    /**
     * Cache token attributes only; Sanctum still checks expiration and loads the user.
     */
    #[\Override]
    public static function findToken($token)
    {
        if (str_contains($token, '|')) {
            [$id, $secret] = explode('|', $token, 2);

            if (! ctype_digit($id) || $secret === '') {
                return null;
            }

            // Sanctum accepts leading zeros; all ID aliases must share invalidation.
            $key = 'personal-access-token:'.(ltrim($id, '0') ?: '0');
        } else {
            $secret = $token;
            $key = 'personal-access-token:hash:'.hash('sha256', $secret);
        }

        $cache = static::tokenCache();
        $attributes = $cache->get($key);

        if ($attributes !== null) {
            $model = new static;
            $instance = $model->newFromBuilder($attributes, $model->getConnection()->getName());

            return hash_equals($instance->token, hash('sha256', $secret)) ? $instance : null;
        }

        // Start the lifetime before the query so an in-flight lookup cannot extend it.
        $cacheUntil = now()->addSeconds(static::$ttl);
        $instance = parent::findToken($token);

        if ($instance === null) {
            return null;
        }

        if ($instance->expires_at !== null && $instance->expires_at->lt($cacheUntil)) {
            $cacheUntil = $instance->expires_at;
        }

        if ($expiration = config('sanctum.expiration')) {
            $cacheUntil = $instance->created_at->copy()->addMinutes($expiration)->min($cacheUntil);
        }

        if ($cacheUntil->isFuture()) {
            $cache->put($key, $instance->getAttributes(), $cacheUntil);
        }

        return $instance;
    }

    protected static function tokenCache(): Repository
    {
        return Cache::store(config('sanctum.cache.store', 'redis'));
    }

    protected function forgetCachedToken(): void
    {
        $cache = static::tokenCache();
        $cache->forget('personal-access-token:'.$this->getKey());

        foreach (array_unique([$this->token, $this->getRawOriginal('token')]) as $hash) {
            $cache->forget('personal-access-token:hash:'.$hash);
        }
    }

    /**
     * Bootstrap the model and its traits.
     */
    #[\Override]
    protected static function boot(): void
    {
        parent::boot();

        static::updating(static function (self $personalAccessToken) {
            // Security-sensitive changes must save normally and invalidate the lookup cache.
            if (array_diff(array_keys($personalAccessToken->getDirty()), ['last_used_at', 'updated_at'])) {
                return;
            }

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

        static::updated(static function (self $personalAccessToken) {
            $personalAccessToken->forgetCachedToken();
        });

        static::deleted(static function (self $personalAccessToken) {
            $personalAccessToken->forgetCachedToken();
            Cache::forget(sprintf('personal-access-token:%s:last_used_at', $personalAccessToken->id));
            Cache::forget(sprintf('personal-access-token:%s:tokenable', $personalAccessToken->id));
        });
    }
}

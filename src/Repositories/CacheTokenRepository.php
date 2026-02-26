<?php

namespace Aerni\Spotify\Repositories;

use Aerni\Spotify\Contracts\TokenRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Cache-backed token repository.
 *
 * NOTE: This implementation is NOT durable across server restarts.
 * If your application restarts, all stored tokens will be lost and users
 * will need to re-authenticate. Use DatabaseTokenRepository for durability.
 */
class CacheTokenRepository implements TokenRepositoryInterface
{
    public function store(string|int $userId, array $tokens): void
    {
        $expiresAt = Carbon::now()->addSeconds($tokens['expires_in']);
        $ttl = (int) $tokens['expires_in'];

        Cache::put("spotify_user_access_token_{$userId}", $tokens['access_token'], $ttl);
        Cache::put("spotify_user_expires_at_{$userId}", $expiresAt->toDateTimeString(), $ttl + 60);

        if (! empty($tokens['refresh_token'])) {
            // Refresh tokens don't expire, store for a long time
            Cache::put("spotify_user_refresh_token_{$userId}", $tokens['refresh_token'], now()->addDays(60));
        }
    }

    public function getAccessToken(string|int $userId): ?string
    {
        return Cache::get("spotify_user_access_token_{$userId}");
    }

    public function getRefreshToken(string|int $userId): ?string
    {
        return Cache::get("spotify_user_refresh_token_{$userId}");
    }

    public function isAccessTokenExpired(string|int $userId): bool
    {
        $expiresAt = Cache::get("spotify_user_expires_at_{$userId}");

        if (! $expiresAt) {
            return true;
        }

        return Carbon::parse($expiresAt)->isPast();
    }

    public function forget(string|int $userId): void
    {
        Cache::forget("spotify_user_access_token_{$userId}");
        Cache::forget("spotify_user_refresh_token_{$userId}");
        Cache::forget("spotify_user_expires_at_{$userId}");
    }
}

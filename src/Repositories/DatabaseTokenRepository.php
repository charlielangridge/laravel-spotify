<?php

namespace Aerni\Spotify\Repositories;

use Aerni\Spotify\Contracts\TokenRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DatabaseTokenRepository implements TokenRepositoryInterface
{
    public function store(string|int $userId, array $tokens): void
    {
        $now = Carbon::now();
        $expiresAt = $now->copy()->addSeconds($tokens['expires_in']);

        $exists = DB::table('spotify_tokens')
            ->where('user_id', (string) $userId)
            ->exists();

        if ($exists) {
            DB::table('spotify_tokens')
                ->where('user_id', (string) $userId)
                ->update([
                    'access_token' => $tokens['access_token'],
                    'refresh_token' => $tokens['refresh_token'] ?? null,
                    'expires_at' => $expiresAt,
                    'scopes' => $tokens['scope'] ?? null,
                    'updated_at' => $now,
                ]);
        } else {
            DB::table('spotify_tokens')->insert([
                'user_id' => (string) $userId,
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'] ?? null,
                'expires_at' => $expiresAt,
                'scopes' => $tokens['scope'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function getAccessToken(string|int $userId): ?string
    {
        $record = DB::table('spotify_tokens')
            ->where('user_id', (string) $userId)
            ->first();

        return $record?->access_token;
    }

    public function getRefreshToken(string|int $userId): ?string
    {
        $record = DB::table('spotify_tokens')
            ->where('user_id', (string) $userId)
            ->first();

        return $record?->refresh_token;
    }

    public function isAccessTokenExpired(string|int $userId): bool
    {
        $record = DB::table('spotify_tokens')
            ->where('user_id', (string) $userId)
            ->first();

        if (! $record || ! $record->expires_at) {
            return true;
        }

        return Carbon::parse($record->expires_at)->isPast();
    }

    public function forget(string|int $userId): void
    {
        DB::table('spotify_tokens')
            ->where('user_id', (string) $userId)
            ->delete();
    }
}

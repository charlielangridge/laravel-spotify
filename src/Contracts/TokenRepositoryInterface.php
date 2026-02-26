<?php

namespace Aerni\Spotify\Contracts;

interface TokenRepositoryInterface
{
    public function store(string|int $userId, array $tokens): void;

    public function getAccessToken(string|int $userId): ?string;

    public function getRefreshToken(string|int $userId): ?string;

    public function isAccessTokenExpired(string|int $userId): bool;

    public function forget(string|int $userId): void;
}

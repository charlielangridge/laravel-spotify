<?php

namespace Aerni\Spotify\Tests\Unit;

use Aerni\Spotify\Repositories\DatabaseTokenRepository;
use Aerni\Spotify\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class DatabaseTokenRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private DatabaseTokenRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new DatabaseTokenRepository;
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

    private function sampleTokens(int $expiresIn = 3600): array
    {
        return [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires_in' => $expiresIn,
            'token_type' => 'Bearer',
            'scope' => 'user-read-email',
        ];
    }

    public function test_can_store_and_retrieve_tokens(): void
    {
        $this->repository->store(1, $this->sampleTokens());

        $this->assertEquals('test-access-token', $this->repository->getAccessToken(1));
        $this->assertEquals('test-refresh-token', $this->repository->getRefreshToken(1));
    }

    public function test_access_token_is_expired_after_expires_at(): void
    {
        Carbon::setTestNow(Carbon::now()->subHour());
        $this->repository->store(1, $this->sampleTokens(1));
        Carbon::setTestNow(null);

        $this->assertTrue($this->repository->isAccessTokenExpired(1));
    }

    public function test_access_token_is_not_expired_before_expires_at(): void
    {
        $this->repository->store(1, $this->sampleTokens(3600));

        $this->assertFalse($this->repository->isAccessTokenExpired(1));
    }

    public function test_can_forget_tokens(): void
    {
        $this->repository->store(1, $this->sampleTokens());
        $this->repository->forget(1);

        $this->assertNull($this->repository->getAccessToken(1));
        $this->assertNull($this->repository->getRefreshToken(1));
    }

    public function test_store_overwrites_existing_tokens(): void
    {
        $this->repository->store(1, $this->sampleTokens());

        $updatedTokens = [
            'access_token' => 'new-access-token',
            'refresh_token' => 'new-refresh-token',
            'expires_in' => 3600,
            'token_type' => 'Bearer',
        ];

        $this->repository->store(1, $updatedTokens);

        $this->assertEquals('new-access-token', $this->repository->getAccessToken(1));
        $this->assertEquals('new-refresh-token', $this->repository->getRefreshToken(1));
    }
}

<?php

namespace Aerni\Spotify\Tests\Unit;

use Aerni\Spotify\Contracts\TokenRepositoryInterface;
use Aerni\Spotify\Exceptions\SpotifyAuthException;
use Aerni\Spotify\Facades\SpotifyClient;
use Aerni\Spotify\SpotifyAuthorizationCode;
use Aerni\Spotify\Tests\TestCase;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery;

class SpotifyAuthorizationCodeTest extends TestCase
{
    private SpotifyAuthorizationCode $auth;

    private TokenRepositoryInterface $tokenRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tokenRepository = Mockery::mock(TokenRepositoryInterface::class);

        $this->auth = new SpotifyAuthorizationCode(
            clientId: 'test-client-id',
            clientSecret: 'test-client-secret',
            redirectUri: 'https://example.com/callback',
            tokens: $this->tokenRepository,
        );
    }

    public function test_can_get_authorization_url(): void
    {
        $url = $this->auth->getAuthorizationUrl(
            scopes: ['user-read-email', 'playlist-read-private'],
            state: 'random-state-123',
        );

        $this->assertStringContainsString('response_type=code', $url);
        $this->assertStringContainsString('client_id=test-client-id', $url);
        $this->assertStringContainsString('redirect_uri=', $url);
        $this->assertStringContainsString('scope=', $url);
        $this->assertStringContainsString('state=random-state-123', $url);
        $this->assertStringContainsString('https://accounts.spotify.com/authorize', $url);
    }

    public function test_can_exchange_code_for_tokens(): void
    {
        $tokenData = [
            'access_token' => 'new-access-token',
            'refresh_token' => 'new-refresh-token',
            'expires_in' => 3600,
            'token_type' => 'Bearer',
            'scope' => 'user-read-email',
        ];

        SpotifyClient::shouldReceive('post')
            ->once()
            ->andReturn($this->spotifyJsonResponse($tokenData));

        $this->tokenRepository
            ->shouldReceive('store')
            ->once()
            ->with(42, $tokenData);

        $result = $this->auth->exchangeCodeForTokens(42, 'auth-code-xyz');

        $this->assertArrayHasKey('access_token', $result);
        $this->assertArrayHasKey('refresh_token', $result);
        $this->assertEquals('new-access-token', $result['access_token']);
    }

    public function test_returns_valid_access_token_when_not_expired(): void
    {
        $this->tokenRepository
            ->shouldReceive('isAccessTokenExpired')
            ->once()
            ->with(1)
            ->andReturn(false);

        $this->tokenRepository
            ->shouldReceive('getAccessToken')
            ->once()
            ->with(1)
            ->andReturn('valid-access-token');

        SpotifyClient::shouldNotReceive('post');

        $token = $this->auth->getAccessTokenForUser(1);

        $this->assertEquals('valid-access-token', $token);
    }

    public function test_auto_refreshes_expired_token(): void
    {
        $refreshedTokenData = [
            'access_token' => 'refreshed-access-token',
            'refresh_token' => 'existing-refresh-token',
            'expires_in' => 3600,
            'token_type' => 'Bearer',
        ];

        $this->tokenRepository
            ->shouldReceive('isAccessTokenExpired')
            ->once()
            ->with(1)
            ->andReturn(true);

        $this->tokenRepository
            ->shouldReceive('getRefreshToken')
            ->twice()
            ->with(1)
            ->andReturn('existing-refresh-token');

        SpotifyClient::shouldReceive('post')
            ->once()
            ->andReturn($this->spotifyJsonResponse($refreshedTokenData));

        $this->tokenRepository
            ->shouldReceive('store')
            ->once()
            ->with(1, $refreshedTokenData);

        $token = $this->auth->getAccessTokenForUser(1);

        $this->assertEquals('refreshed-access-token', $token);
    }

    public function test_throws_exception_when_no_refresh_token(): void
    {
        $this->tokenRepository
            ->shouldReceive('isAccessTokenExpired')
            ->once()
            ->with(1)
            ->andReturn(true);

        $this->tokenRepository
            ->shouldReceive('getRefreshToken')
            ->once()
            ->with(1)
            ->andReturn(null);

        $this->expectException(SpotifyAuthException::class);

        $this->auth->getAccessTokenForUser(1);
    }

    public function test_can_forget_tokens(): void
    {
        $this->tokenRepository
            ->shouldReceive('forget')
            ->once()
            ->with(1);

        $this->auth->forgetTokens(1);
    }

    public function test_throws_exception_on_invalid_code_exchange(): void
    {
        $guzzleRequest = new Request('POST', 'https://accounts.spotify.com/api/token');
        $guzzleResponse = new Response(400, ['Content-Type' => 'application/json'], json_encode(['error' => 'invalid_grant']));

        SpotifyClient::shouldReceive('post')
            ->once()
            ->andThrow(new RequestException('Bad Request', $guzzleRequest, $guzzleResponse));

        $this->expectException(SpotifyAuthException::class);

        $this->auth->exchangeCodeForTokens(1, 'bad-code');
    }
}

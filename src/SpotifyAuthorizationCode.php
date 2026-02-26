<?php

namespace Aerni\Spotify;

use Aerni\Spotify\Contracts\TokenRepositoryInterface;
use Aerni\Spotify\Exceptions\SpotifyAuthException;
use Aerni\Spotify\Facades\SpotifyClient;
use GuzzleHttp\Exception\RequestException;

class SpotifyAuthorizationCode
{
    private const SPOTIFY_AUTHORIZE_URL = 'https://accounts.spotify.com/authorize';

    private const SPOTIFY_API_TOKEN_URL = 'https://accounts.spotify.com/api/token';

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $redirectUri,
        private readonly TokenRepositoryInterface $tokens,
    ) {}

    /**
     * Generate the Spotify authorization URL to redirect the user to.
     */
    public function getAuthorizationUrl(array $scopes = [], ?string $state = null): string
    {
        $params = [
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
        ];

        if (! empty($scopes)) {
            $params['scope'] = implode(' ', $scopes);
        }

        if ($state !== null) {
            $params['state'] = $state;
        }

        return self::SPOTIFY_AUTHORIZE_URL.'?'.http_build_query($params);
    }

    /**
     * Exchange an authorization code for access and refresh tokens, then store them.
     *
     * @throws SpotifyAuthException
     */
    public function exchangeCodeForTokens(string|int $userId, string $code): array
    {
        try {
            $response = SpotifyClient::post(self::SPOTIFY_API_TOKEN_URL, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accepts' => 'application/json',
                    'Authorization' => 'Basic '.base64_encode($this->clientId.':'.$this->clientSecret),
                ],
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $this->redirectUri,
                ],
            ]);
        } catch (RequestException $e) {
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents());
            $status = $e->getCode();
            $message = $errorResponse->error ?? 'Unknown error';

            throw new SpotifyAuthException($message, $status, $errorResponse);
        }

        $tokens = json_decode((string) $response->getBody(), true);

        $this->tokens->store($userId, $tokens);

        return $tokens;
    }

    /**
     * Get a valid access token for the user, auto-refreshing if expired.
     *
     * @throws SpotifyAuthException
     */
    public function getAccessTokenForUser(string|int $userId): string
    {
        if (! $this->tokens->isAccessTokenExpired($userId)) {
            return $this->tokens->getAccessToken($userId);
        }

        $refreshToken = $this->tokens->getRefreshToken($userId);

        if ($refreshToken === null) {
            throw new SpotifyAuthException('No refresh token available. User must re-authenticate.', 401);
        }

        $tokens = $this->refreshAccessToken($userId);

        return $tokens['access_token'];
    }

    /**
     * Refresh the access token using the stored refresh token.
     *
     * @throws SpotifyAuthException
     */
    public function refreshAccessToken(string|int $userId): array
    {
        $refreshToken = $this->tokens->getRefreshToken($userId);

        if ($refreshToken === null) {
            throw new SpotifyAuthException('No refresh token available. User must re-authenticate.', 401);
        }

        try {
            $response = SpotifyClient::post(self::SPOTIFY_API_TOKEN_URL, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accepts' => 'application/json',
                    'Authorization' => 'Basic '.base64_encode($this->clientId.':'.$this->clientSecret),
                ],
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                ],
            ]);
        } catch (RequestException $e) {
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents());
            $status = $e->getCode();
            $message = $errorResponse->error ?? 'Unknown error';

            throw new SpotifyAuthException($message, $status, $errorResponse);
        }

        $tokens = json_decode((string) $response->getBody(), true);

        // Spotify may not return a new refresh_token — keep the existing one
        if (empty($tokens['refresh_token'])) {
            $tokens['refresh_token'] = $refreshToken;
        }

        $this->tokens->store($userId, $tokens);

        return $tokens;
    }

    /**
     * Remove stored tokens for the user (logout / revoke).
     */
    public function forgetTokens(string|int $userId): void
    {
        $this->tokens->forget($userId);
    }
}

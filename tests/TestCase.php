<?php

namespace Aerni\Spotify\Tests;

use Aerni\Spotify\Facades\SpotifyClient;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Support\Facades\Cache;
use Orchestra\Testbench\TestCase as Orchestra;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget('spotifyAccessToken');
        $this->mockSpotifyAuthToken();
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Load the .env file
        $app->useEnvironmentPath(__DIR__.'/..');
        $app->bootstrapWith([LoadEnvironmentVariables::class]);
        parent::getEnvironmentSetUp($app);

        // Set the config with the provided .env variables
        $app['config']->set('spotify', require (__DIR__.'/../config/spotify.php'));

        $app['config']->set('spotify.default_config', [
            'country' => 'US',
            'locale' => 'en_US',
            'market' => 'US',
        ]);
    }

    protected function getPackageProviders($app): array
    {
        return [
            'Aerni\Spotify\SpotifyServiceProvider',
        ];
    }

    protected function mockSpotifyAuthToken(string $token = 'mocked-access-token'): void
    {
        SpotifyClient::shouldReceive('post')
            ->byDefault()
            ->andReturn($this->spotifyJsonResponse([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ]));
    }

    protected function mockSpotifyApi(array $responses): void
    {
        SpotifyClient::shouldReceive('get')
            ->andReturnUsing(function (string $url, array $options = []) use ($responses): ResponseInterface {
                foreach ($responses as $pattern => $response) {
                    if (! str_contains($url, $pattern)) {
                        continue;
                    }

                    if (is_callable($response)) {
                        $response = $response($url, $options);
                    }

                    if ($response instanceof Throwable) {
                        throw $response;
                    }

                    if ($response instanceof ResponseInterface) {
                        return $response;
                    }

                    return $this->spotifyJsonResponse($response);
                }

                throw new RuntimeException("No mocked Spotify response configured for URL [{$url}].");
            });
    }

    protected function mockSpotifyApiWrite(string $httpMethod, array $responses): void
    {
        SpotifyClient::shouldReceive(strtolower($httpMethod))
            ->andReturnUsing(function (string $url, array $options = []) use ($responses): ResponseInterface {
                foreach ($responses as $pattern => $response) {
                    if (! str_contains($url, $pattern)) {
                        continue;
                    }

                    if (is_callable($response)) {
                        $response = $response($url, $options);
                    }

                    if ($response instanceof Throwable) {
                        throw $response;
                    }

                    if ($response instanceof ResponseInterface) {
                        return $response;
                    }

                    return $this->spotifyJsonResponse($response);
                }

                throw new RuntimeException("No mocked Spotify response configured for URL [{$url}].");
            });
    }

    protected function spotifyJsonResponse(array $body, int $status = 200): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode($body, JSON_THROW_ON_ERROR));
    }

    protected function queryParam(string $url, string $name, $default = null)
    {
        $query = parse_url($url, PHP_URL_QUERY);

        if (! is_string($query)) {
            return $default;
        }

        parse_str($query, $params);

        return $params[$name] ?? $default;
    }
}

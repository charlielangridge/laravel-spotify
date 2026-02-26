<?php

namespace Aerni\Spotify\Tests\Unit;

use Aerni\Spotify\Exceptions\SpotifyApiException;
use Aerni\Spotify\Exceptions\SpotifyAuthException;
use Aerni\Spotify\Facades\SpotifyClient;
use Aerni\Spotify\Facades\Spotify;
use Aerni\Spotify\SpotifyAuth;
use Aerni\Spotify\SpotifyRequest;
use Aerni\Spotify\Tests\TestCase;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class SpotifyExceptionTest extends TestCase
{
    public function test_can_throw_auth_exception(): void
    {
        $auth = new SpotifyAuth('123', '123');
        $request = new Request('POST', 'https://accounts.spotify.com/api/token');
        $response = new Response(401, ['Content-Type' => 'application/json'], json_encode(['error' => 'invalid_client']));

        SpotifyClient::shouldReceive('post')
            ->once()
            ->andThrow(new RequestException('Unauthorized', $request, $response));

        $this->expectException(SpotifyAuthException::class);
        $this->expectExceptionMessage('invalid_client');

        $auth->getAccessToken();
    }

    public function test_can_throw_api_exception(): void
    {
        $request = resolve(SpotifyRequest::class);
        $guzzleRequest = new Request('GET', 'https://api.spotify.com/v1/not-existing-endpoint');
        $guzzleResponse = new Response(404, ['Content-Type' => 'application/json'], json_encode(['error' => ['message' => 'Not Found']]));

        $this->mockSpotifyApi([
            '/not-existing-endpoint' => new RequestException('Not Found', $guzzleRequest, $guzzleResponse),
        ]);

        $this->expectException(SpotifyApiException::class);
        $this->expectExceptionMessage('Not Found');

        $request->get('/not-existing-endpoint');
    }

    public function test_can_get_api_response_from_exception(): void
    {
        $guzzleRequest = new Request('GET', 'https://api.spotify.com/v1/tracks/6RTOAaQeVkm1GUTqY0hjp');
        $guzzleResponse = new Response(404, ['Content-Type' => 'application/json'], json_encode(['error' => ['message' => 'Not Found']]));
        $apiResponse = null;

        $this->mockSpotifyApi([
            '/tracks/6RTOAaQeVkm1GUTqY0hjp' => new RequestException('Not Found', $guzzleRequest, $guzzleResponse),
        ]);

        try {
            Spotify::track('6RTOAaQeVkm1GUTqY0hjp')->get();
        } catch (SpotifyApiException $e) {
            $apiResponse = $e->getApiResponse();
        }

        $this->assertInstanceOf(Response::class, $apiResponse);
    }
}

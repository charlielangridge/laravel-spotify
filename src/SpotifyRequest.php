<?php

namespace Aerni\Spotify;

use Aerni\Spotify\Exceptions\SpotifyApiException;
use Aerni\Spotify\Facades\SpotifyClient;
use GuzzleHttp\Exception\RequestException;

class SpotifyRequest
{
    private $accessToken;

    private $apiUrl;

    public function __construct(string $accessToken)
    {
        $this->accessToken = $accessToken;
        $this->apiUrl = config('spotify.api_url', 'https://api.spotify.com/v1');
    }

    /**
     * Make the API request.
     *
     * @throws SpotifyApiException
     */
    public function get(string $endpoint, array $params = []): array
    {
        try {
            $response = SpotifyClient::get($this->apiUrl.$endpoint.'?'.http_build_query($params), [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accepts' => 'application/json',
                    'Authorization' => 'Bearer '.$this->accessToken,
                ],
            ]);
        } catch (RequestException $e) {
            $errorResponse = $e->getResponse();
            $status = $errorResponse->getStatusCode();

            $message = $errorResponse->getReasonPhrase();

            throw new SpotifyApiException($message, $status, $errorResponse);
        }

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * Make a POST API request.
     *
     * @throws SpotifyApiException
     */
    public function post(string $endpoint, array $body = []): array
    {
        try {
            $response = SpotifyClient::post($this->apiUrl.$endpoint, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$this->accessToken,
                ],
                'json' => $body,
            ]);
        } catch (RequestException $e) {
            $errorResponse = $e->getResponse();
            $status = $errorResponse->getStatusCode();
            $message = $errorResponse->getReasonPhrase();

            throw new SpotifyApiException($message, $status, $errorResponse);
        }

        return json_decode((string) $response->getBody(), true) ?? [];
    }

    /**
     * Make a PUT API request.
     *
     * @throws SpotifyApiException
     */
    public function put(string $endpoint, array $body = []): array
    {
        try {
            $response = SpotifyClient::put($this->apiUrl.$endpoint, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$this->accessToken,
                ],
                'json' => $body,
            ]);
        } catch (RequestException $e) {
            $errorResponse = $e->getResponse();
            $status = $errorResponse->getStatusCode();
            $message = $errorResponse->getReasonPhrase();

            throw new SpotifyApiException($message, $status, $errorResponse);
        }

        return json_decode((string) $response->getBody(), true) ?? [];
    }

    /**
     * Make a DELETE API request.
     *
     * @throws SpotifyApiException
     */
    public function delete(string $endpoint, array $body = []): array
    {
        try {
            $response = SpotifyClient::delete($this->apiUrl.$endpoint, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$this->accessToken,
                ],
                'json' => $body,
            ]);
        } catch (RequestException $e) {
            $errorResponse = $e->getResponse();
            $status = $errorResponse->getStatusCode();
            $message = $errorResponse->getReasonPhrase();

            throw new SpotifyApiException($message, $status, $errorResponse);
        }

        return json_decode((string) $response->getBody(), true) ?? [];
    }
}

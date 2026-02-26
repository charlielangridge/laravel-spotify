<?php

namespace Aerni\Spotify\Tests\Unit;

use Aerni\Spotify\SpotifyRequest;
use Aerni\Spotify\Tests\TestCase;

class SpotifyRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockSpotifyApi([
            '/tracks/35GACeX8Zl55jp29xFbvvo' => [
                'id' => '35GACeX8Zl55jp29xFbvvo',
                'name' => 'Mock Track',
                'type' => 'track',
            ],
        ]);
    }

    public function test_can_make_request_and_get_response(): void
    {
        $request = resolve(SpotifyRequest::class);

        $response = $request->get('/tracks/35GACeX8Zl55jp29xFbvvo');

        $this->assertIsArray($response);
    }
}

<?php

namespace Aerni\Spotify\Tests\Unit;

use Aerni\Spotify\SpotifyAuth;
use Aerni\Spotify\Tests\TestCase;

class SpotifyAuthTest extends TestCase
{
    public function test_can_get_access_token(): void
    {
        $auth = resolve(SpotifyAuth::class);

        $accessToken = $auth->getAccessToken();

        $this->assertEquals('mocked-access-token', $accessToken);
    }
}

<?php

namespace Aerni\Spotify\Tests\Unit;

use Aerni\Spotify\Facades\Spotify;
use Aerni\Spotify\Tests\TestCase;

class ArtistsTest extends TestCase
{
    private $artistId = '3hyTRrdgrNuAExA3tNS8CA';

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockSpotifyApi([
            '/artists/'.$this->artistId.'?' => [
                'id' => $this->artistId,
                'name' => 'Mock Artist',
                'type' => 'artist',
            ],
            '/artists/'.$this->artistId.'/albums/' => [
                'href' => 'https://api.spotify.com/v1/artists/'.$this->artistId.'/albums',
                'items' => [['id' => 'album-1', 'name' => 'Album 1', 'type' => 'album']],
                'limit' => 20,
                'offset' => 0,
                'total' => 1,
            ],
        ]);
    }

    public function test_can_get_an_artist(): void
    {
        $artist = Spotify::artist($this->artistId)->get();

        $this->assertEquals($artist['id'], $this->artistId);
    }

    public function test_can_get_artist_albums(): void
    {
        $albums = Spotify::artistAlbums($this->artistId)->get();

        $this->assertArrayHasKey('items', $albums);
    }
}

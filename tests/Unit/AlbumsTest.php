<?php

namespace Aerni\Spotify\Tests\Unit;

use Aerni\Spotify\Facades\Spotify;
use Aerni\Spotify\Tests\TestCase;

class AlbumsTest extends TestCase
{
    private $albumId = '1Dm5rDVBBeLLjqfzBkuadR';

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockSpotifyApi([
            '/albums/'.$this->albumId.'?' => [
                'id' => $this->albumId,
                'name' => 'Mock Album',
                'type' => 'album',
            ],
            '/albums/'.$this->albumId.'/tracks/' => [
                'href' => 'https://api.spotify.com/v1/albums/'.$this->albumId.'/tracks',
                'items' => [['id' => 'track-1', 'name' => 'Track 1', 'type' => 'track']],
                'limit' => 20,
                'offset' => 0,
                'total' => 1,
            ],
        ]);
    }

    public function test_can_get_an_album(): void
    {
        $album = Spotify::album($this->albumId)->get();

        $this->assertEquals($album['id'], $this->albumId);
    }

    public function test_can_get_album_tracks(): void
    {
        $tracks = Spotify::albumTracks($this->albumId)->get();

        $this->assertArrayHasKey('items', $tracks);
    }
}

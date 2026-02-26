<?php

namespace Aerni\Spotify\Tests\Unit;

use Aerni\Spotify\Facades\Spotify;
use Aerni\Spotify\Tests\TestCase;

class AlbumsTest extends TestCase
{
    private $albumId = '1Dm5rDVBBeLLjqfzBkuadR';

    private $albumIds = ['1Dm5rDVBBeLLjqfzBkuadR', '5phxHbK2GSr7hEu4orLywP', '3WEwS5DLsagnqQtHP2oEEu'];

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
            '/albums/?' => [
                'albums' => array_map(fn ($id) => ['id' => $id, 'type' => 'album'], $this->albumIds),
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

    public function test_can_get_several_albums(): void
    {
        $album = Spotify::albums($this->albumIds)->get();
        $albumId = $album['albums'][0]['id'];

        $this->assertEquals($this->albumIds[0], $albumId);
    }
}

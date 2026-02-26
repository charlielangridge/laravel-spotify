<?php

namespace Aerni\Spotify\Tests\Unit;

use Aerni\Spotify\Facades\Spotify;
use Aerni\Spotify\Tests\TestCase;

class ArtistsTest extends TestCase
{
    private $artistId = '3hyTRrdgrNuAExA3tNS8CA';

    private $artistIds = ['0ADKN6ZiuyyScOTXloddx9', '3hyTRrdgrNuAExA3tNS8CA', '2FNOMU2OOusxW671wZKbKt'];

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
            '/artists/'.$this->artistId.'/top-tracks/' => [
                'tracks' => [[
                    'id' => 'track-1',
                    'name' => 'Top Track',
                    'artists' => [['id' => $this->artistId, 'name' => 'Mock Artist']],
                ]],
            ],
            '/artists/?' => [
                'artists' => array_map(fn ($id) => ['id' => $id, 'type' => 'artist'], $this->artistIds),
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

    public function test_can_get_artist_top_tracks(): void
    {
        $tracks = Spotify::artistTopTracks($this->artistId)->get();
        $artist = $tracks['tracks'][0]['artists'][0]['id'];

        $this->assertEquals($artist, $this->artistId);
    }

    public function test_can_get_several_artists(): void
    {
        $artists = Spotify::artists($this->artistIds)->get();
        $artistId = $artists['artists'][0]['id'];

        $this->assertEquals($this->artistIds[0], $artistId);
    }
}

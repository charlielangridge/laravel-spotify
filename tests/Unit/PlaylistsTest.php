<?php

namespace Aerni\Spotify\Tests\Unit;

use Aerni\Spotify\Facades\Spotify;
use Aerni\Spotify\Tests\TestCase;

class PlaylistsTest extends TestCase
{
    private $playlistId = '1NLLcKrGXII2F2oRKZVatw';

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockSpotifyApi([
            '/playlists/'.$this->playlistId.'/images/' => [[
                'url' => 'https://i.scdn.co/image/playlist-cover',
                'height' => null,
                'width' => null,
            ]],
            '/playlists/'.$this->playlistId.'/tracks/' => function (string $url): array {
                $limit = (int) $this->queryParam($url, 'limit', 20);
                $offset = (int) $this->queryParam($url, 'offset', 0);

                return [
                    'href' => 'https://api.spotify.com/v1/playlists/'.$this->playlistId.'/tracks',
                    'items' => [['track' => ['id' => 'track-1', 'name' => 'Track 1', 'type' => 'track']]],
                    'limit' => $limit,
                    'offset' => $offset,
                    'total' => 1,
                ];
            },
            '/playlists/'.$this->playlistId.'?' => [
                'id' => $this->playlistId,
                'name' => 'Mock Playlist',
                'type' => 'playlist',
            ],
        ]);
    }

    public function test_can_get_playlist_cover_image(): void
    {
        $coverImage = Spotify::playlistCoverImage($this->playlistId)->get();

        $this->assertArrayHasKey('url', $coverImage[0]);
    }

    public function test_can_get_a_playlist(): void
    {
        $playlist = Spotify::playlist($this->playlistId)->get();

        $this->assertEquals($playlist['id'], $this->playlistId);
    }

    public function test_can_get_playlist_tracks(): void
    {
        $playlistTracks = Spotify::playlistTracks($this->playlistId)->limit(50)->offset(10)->get();

        $this->assertArrayHasKey('items', $playlistTracks);
        $this->assertEquals(50, $playlistTracks['limit']);
        $this->assertEquals(10, $playlistTracks['offset']);
    }
}

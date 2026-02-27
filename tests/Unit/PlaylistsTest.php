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
            '/playlists/'.$this->playlistId.'/items/' => function (string $url): array {
                $limit = (int) $this->queryParam($url, 'limit', 20);
                $offset = (int) $this->queryParam($url, 'offset', 0);

                return [
                    'href' => 'https://api.spotify.com/v1/playlists/'.$this->playlistId.'/items',
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

    public function test_can_get_playlist_items(): void
    {
        $playlistItems = Spotify::playlistItems($this->playlistId)->limit(50)->offset(10)->get();

        $this->assertArrayHasKey('items', $playlistItems);
        $this->assertEquals(50, $playlistItems['limit']);
        $this->assertEquals(10, $playlistItems['offset']);
    }

    public function test_can_add_playlist_items(): void
    {
        $uris = ['spotify:track:abc123', 'spotify:track:def456'];

        $this->mockSpotifyApiWrite('POST', [
            '/playlists/'.$this->playlistId.'/items' => ['snapshot_id' => 'mock-snapshot-add'],
        ]);

        $result = Spotify::withToken('mock-token')
            ->addPlaylistItems($this->playlistId, $uris)
            ->get();

        $this->assertEquals('mock-snapshot-add', $result['snapshot_id']);
    }

    public function test_can_update_playlist_items(): void
    {
        $uris = ['spotify:track:abc123'];

        $this->mockSpotifyApiWrite('PUT', [
            '/playlists/'.$this->playlistId.'/items' => ['snapshot_id' => 'mock-snapshot-update'],
        ]);

        $result = Spotify::withToken('mock-token')
            ->updatePlaylistItems($this->playlistId, $uris, 0, 1, 2)
            ->get();

        $this->assertEquals('mock-snapshot-update', $result['snapshot_id']);
    }

    public function test_can_remove_playlist_items(): void
    {
        $uris = ['spotify:track:abc123'];

        $this->mockSpotifyApiWrite('DELETE', [
            '/playlists/'.$this->playlistId.'/items' => ['snapshot_id' => 'mock-snapshot-remove'],
        ]);

        $result = Spotify::withToken('mock-token')
            ->removePlaylistItems($this->playlistId, $uris)
            ->get();

        $this->assertEquals('mock-snapshot-remove', $result['snapshot_id']);
    }
}

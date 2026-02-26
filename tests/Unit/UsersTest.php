<?php

namespace Aerni\Spotify\Tests\Unit;

use Aerni\Spotify\Facades\Spotify;
use Aerni\Spotify\Tests\TestCase;

class UsersTest extends TestCase
{
    private $userId = '21drtyolp7mfwvb2fpoexyaqq';

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockSpotifyApi([
            '/users/'.$this->userId.'?' => [
                'id' => $this->userId,
                'display_name' => 'Mock User',
                'type' => 'user',
            ],
            '/users/'.$this->userId.'/playlists' => [
                'href' => 'https://api.spotify.com/v1/users/'.$this->userId.'/playlists',
                'items' => [['id' => 'playlist-1', 'name' => 'Playlist 1', 'type' => 'playlist']],
                'limit' => 20,
                'offset' => 0,
                'total' => 1,
            ],
        ]);
    }

    public function test_can_get_a_user(): void
    {
        $user = Spotify::user($this->userId)->get();

        $this->assertEquals($user['id'], $this->userId);
    }

    public function test_can_get_user_playlists(): void
    {
        $playlists = Spotify::userPlaylists($this->userId)->get();

        $this->assertArrayHasKey('items', $playlists);
    }
}

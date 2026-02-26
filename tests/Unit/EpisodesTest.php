<?php

namespace Aerni\Spotify\Tests\Unit;

use Aerni\Spotify\Facades\Spotify;
use Aerni\Spotify\Tests\TestCase;

class EpisodesTest extends TestCase
{
    private $episodeId = '0bA111JKkvh84dM2evLENB';

    private $episodeIds = ['0bA111JKkvh84dM2evLENB', '7HLqYy8myk9BduYj6vFDHB', '1iUYyZ5MCBwGPfaJLnILjY'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockSpotifyApi([
            '/episodes/?' => [
                'episodes' => array_map(fn ($id) => ['id' => $id, 'type' => 'episode'], $this->episodeIds),
            ],
            '/episodes/'.$this->episodeId.'?' => [
                'id' => $this->episodeId,
                'name' => 'Mock Episode',
                'type' => 'episode',
            ],
        ]);
    }

    public function test_can_get_several_episodes(): void
    {
        $episodes = Spotify::episodes($this->episodeIds)->get();
        $episodeId = $episodes['episodes'][0]['id'];

        $this->assertEquals($this->episodeIds[0], $episodeId);
    }

    public function test_can_get_a_episode(): void
    {
        $episode = Spotify::episode($this->episodeId)->get();

        $this->assertEquals($episode['id'], $this->episodeId);
    }
}

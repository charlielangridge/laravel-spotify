<?php

namespace Aerni\Spotify\Tests\Unit;

use Aerni\Spotify\Facades\Spotify;
use Aerni\Spotify\Tests\TestCase;

class EpisodesTest extends TestCase
{
    private $episodeId = '0bA111JKkvh84dM2evLENB';

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockSpotifyApi([
            '/episodes/'.$this->episodeId.'?' => [
                'id' => $this->episodeId,
                'name' => 'Mock Episode',
                'type' => 'episode',
            ],
        ]);
    }

    public function test_can_get_a_episode(): void
    {
        $episode = Spotify::episode($this->episodeId)->get();

        $this->assertEquals($episode['id'], $this->episodeId);
    }
}

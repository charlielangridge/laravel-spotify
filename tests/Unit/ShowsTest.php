<?php

namespace Aerni\Spotify\Tests\Unit;

use Aerni\Spotify\Facades\Spotify;
use Aerni\Spotify\Tests\TestCase;

class ShowsTest extends TestCase
{
    private $showId = '488Ctw9jVD7jwwo7vPET14';

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockSpotifyApi([
            '/shows/'.$this->showId.'/episodes/' => [
                'href' => 'https://api.spotify.com/v1/shows/'.$this->showId.'/episodes',
                'items' => [['id' => 'episode-1', 'name' => 'Episode 1', 'type' => 'episode']],
                'limit' => 20,
                'offset' => 0,
                'total' => 1,
            ],
            '/shows/'.$this->showId.'?' => [
                'id' => $this->showId,
                'name' => 'Mock Show',
                'type' => 'show',
            ],
        ]);
    }

    public function test_can_get_a_show(): void
    {
        $show = Spotify::show($this->showId)->get();

        $this->assertEquals($show['id'], $this->showId);
    }

    public function test_can_get_show_episodes(): void
    {
        $episodes = Spotify::showEpisodes($this->showId)->get();

        $this->assertArrayHasKey('items', $episodes);
    }
}

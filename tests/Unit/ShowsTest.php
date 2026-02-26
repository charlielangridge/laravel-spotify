<?php

namespace Aerni\Spotify\Tests\Unit;

use Aerni\Spotify\Facades\Spotify;
use Aerni\Spotify\Tests\TestCase;

class ShowsTest extends TestCase
{
    private $showId = '488Ctw9jVD7jwwo7vPET14';

    private $showIds = ['488Ctw9jVD7jwwo7vPET14', '4rOoJ6Egrf8K2IrywzwOMk', '1Zuurv8AZFWti60lSXiDgz'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockSpotifyApi([
            '/shows/?' => [
                'shows' => array_map(fn ($id) => ['id' => $id, 'type' => 'show'], $this->showIds),
            ],
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

    public function test_can_get_several_shows(): void
    {
        $shows = Spotify::shows($this->showIds)->get();
        $showId = $shows['shows'][0]['id'];

        $this->assertEquals($this->showIds[0], $showId);
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

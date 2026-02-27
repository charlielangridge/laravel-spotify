<?php

namespace Aerni\Spotify\Tests\Unit;

use Aerni\Spotify\Facades\Spotify;
use Aerni\Spotify\Tests\TestCase;

class TracksTest extends TestCase
{
    private $trackId = '35GACeX8Zl55jp29xFbvvo';

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockSpotifyApi([
            '/tracks/'.$this->trackId.'?' => [
                'id' => $this->trackId,
                'name' => 'Mock Track',
                'type' => 'track',
            ],
        ]);
    }

    public function test_can_get_a_track(): void
    {
        $track = Spotify::track($this->trackId)->get();

        $this->assertEquals($track['id'], $this->trackId);
    }
}

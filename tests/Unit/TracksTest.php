<?php

namespace Aerni\Spotify\Tests\Unit;

use Aerni\Spotify\Facades\Spotify;
use Aerni\Spotify\Tests\TestCase;

class TracksTest extends TestCase
{
    private $trackId = '35GACeX8Zl55jp29xFbvvo';

    private $trackIds = ['6RTOAaQeVkm1GUTqIY0hjp', '35GACeX8Zl55jp29xFbvvo', '5yNffCuv0YGOgRazVMfEP6'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockSpotifyApi([
            '/tracks/?' => [
                'tracks' => array_map(fn ($id) => ['id' => $id, 'type' => 'track'], $this->trackIds),
            ],
            '/tracks/'.$this->trackId.'?' => [
                'id' => $this->trackId,
                'name' => 'Mock Track',
                'type' => 'track',
            ],
        ]);
    }

    public function test_can_get_several_tracks(): void
    {
        $tracks = Spotify::tracks($this->trackIds)->get();
        $trackId = $tracks['tracks'][0]['id'];

        $this->assertEquals($this->trackIds[0], $trackId);
    }

    public function test_can_get_a_track(): void
    {
        $track = Spotify::track($this->trackId)->get();

        $this->assertEquals($track['id'], $this->trackId);
    }
}

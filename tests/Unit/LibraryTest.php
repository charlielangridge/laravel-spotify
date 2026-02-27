<?php

namespace Aerni\Spotify\Tests\Unit;

use Aerni\Spotify\Facades\Spotify;
use Aerni\Spotify\Tests\TestCase;

class LibraryTest extends TestCase
{
    public function test_can_check_library_contains(): void
    {
        $uris = ['spotify:track:abc123', 'spotify:track:def456'];

        $this->mockSpotifyApi([
            '/me/library/contains' => [true, false],
        ]);

        $result = Spotify::withToken('mock-token')->libraryContains($uris)->get();

        $this->assertEquals([true, false], $result);
    }

    public function test_can_save_to_library(): void
    {
        $uris = ['spotify:track:abc123'];

        $this->mockSpotifyApiWrite('PUT', [
            '/me/library' => [],
        ]);

        $result = Spotify::withToken('mock-token')->saveToLibrary($uris)->get();

        $this->assertEquals([], $result);
    }

    public function test_can_save_to_library_with_comma_separated_string(): void
    {
        $uris = 'spotify:track:abc123, spotify:track:def456';

        $this->mockSpotifyApiWrite('PUT', [
            '/me/library' => [],
        ]);

        $result = Spotify::withToken('mock-token')->saveToLibrary($uris)->get();

        $this->assertEquals([], $result);
    }

    public function test_can_remove_from_library(): void
    {
        $uris = ['spotify:track:abc123'];

        $this->mockSpotifyApiWrite('DELETE', [
            '/me/library' => [],
        ]);

        $result = Spotify::withToken('mock-token')->removeFromLibrary($uris)->get();

        $this->assertEquals([], $result);
    }
}

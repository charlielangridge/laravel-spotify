<?php

namespace Aerni\Spotify\Tests\Unit;

use Aerni\Spotify\Facades\Spotify;
use Aerni\Spotify\Tests\TestCase;
use Exception;

class SearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockSpotifyApi([
            '/search/' => fn (string $url): array => $this->mockedSearchResponse($url),
        ]);
    }

    public function test_can_search_for_items_and_pass_types_as_an_array(): void
    {
        $query = 'Tremble';
        $typesArray = ['album', 'artist', 'playlist', 'track'];

        $items = Spotify::searchItems($query, $typesArray)->limit(10)->get();

        $this->assertCount(4, $items);
        $this->assertCount(10, $items['tracks']['items']);
        $this->assertEquals(0, $items['tracks']['offset']);
    }

    public function test_can_search_for_items_and_pass_types_as_a_string(): void
    {
        $query = 'Tremble';
        $typesString = 'album, artist, playlist, track';

        $items = Spotify::searchItems($query, $typesString)->limit(10)->get();

        $this->assertCount(4, $items);
        $this->assertCount(10, $items['tracks']['items']);
        $this->assertEquals(0, $items['tracks']['offset']);
    }

    public function test_throws_exception_when_passing_an_argument_that_is_not_valid(): void
    {
        $query = 'Tremble';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Please provide a string with comma-separated values or an array as the argument to the [type] parameter.');

        Spotify::searchItems($query, true)->get();
    }

    public function test_can_search_for_albums(): void
    {
        $query = 'Tremble';

        $albums = Spotify::searchAlbums($query)->limit(10)->offset(20)->get('albums');
        $albumName = $albums['items'][0]['name'];

        $this->assertStringContainsStringIgnoringCase($query, $albumName);
        $this->assertCount(10, $albums['items']);
        $this->assertEquals(20, $albums['offset']);
    }

    public function test_can_search_for_artists(): void
    {
        $query = 'Columbus';

        $artists = Spotify::searchArtists($query)->limit(10)->offset(20)->get();
        $artistName = $artists['artists']['items'][0]['name'];

        $this->assertStringContainsStringIgnoringCase($query, $artistName);
        $this->assertCount(10, $artists['artists']['items']);
        $this->assertEquals(20, $artists['artists']['offset']);
    }

    public function test_can_search_for_episodes(): void
    {
        $query = 'Mike Winger';

        $episodes = Spotify::searchEpisodes($query)->limit(10)->offset(20)->get();
        $episodeName = $episodes['episodes']['items'][0]['name'];

        $this->assertStringContainsStringIgnoringCase($query, $episodeName);
        $this->assertCount(10, $episodes['episodes']['items']);
        $this->assertEquals(20, $episodes['episodes']['offset']);
    }

    public function test_can_search_for_playlists(): void
    {
        $query = 'UK Garage';

        $playlists = Spotify::searchPlaylists($query)->limit(10)->offset(1)->get();
        $playlistName = $playlists['playlists']['items'][0]['name'];

        $this->assertStringContainsStringIgnoringCase($query, $playlistName);
        $this->assertCount(10, $playlists['playlists']['items']);
        $this->assertEquals(1, $playlists['playlists']['offset']);
    }

    public function test_can_search_for_shows(): void
    {
        $query = 'Worship';

        $shows = Spotify::searchShows($query)->limit(10)->offset(20)->get();
        $showName = $shows['shows']['items'][0]['name'];

        $this->assertStringContainsStringIgnoringCase($query, $showName);
        $this->assertCount(10, $shows['shows']['items']);
        $this->assertEquals(20, $shows['shows']['offset']);
    }

    public function test_can_search_for_tracks(): void
    {
        $query = 'Tremble';

        $tracks = Spotify::searchTracks($query)->limit(10)->offset(10)->get();
        $trackName = $tracks['tracks']['items'][0]['name'];

        $this->assertStringContainsStringIgnoringCase($query, $trackName);
        $this->assertCount(10, $tracks['tracks']['items']);
        $this->assertEquals(10, $tracks['tracks']['offset']);
    }

    private function mockedSearchResponse(string $url): array
    {
        $query = (string) $this->queryParam($url, 'q', 'Mock Query');
        $typeParam = (string) $this->queryParam($url, 'type', '');
        $limit = (int) $this->queryParam($url, 'limit', 5);
        $offset = (int) $this->queryParam($url, 'offset', 0);
        $types = array_filter(explode(',', $typeParam));

        $response = [];

        foreach ($types as $type) {
            $key = $type.'s';

            $response[$key] = [
                'href' => 'https://api.spotify.com/v1/search',
                'items' => $this->searchItemsForType($type, $query, $limit),
                'limit' => $limit,
                'offset' => $offset,
                'total' => $limit,
            ];
        }

        return $response;
    }

    private function searchItemsForType(string $type, string $query, int $limit): array
    {
        return array_map(function (int $index) use ($type, $query): array {
            return [
                'id' => $type.'-'.$index,
                'name' => $query.' '.$index,
                'type' => $type,
            ];
        }, range(1, $limit));
    }
}

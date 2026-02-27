<?php

namespace Aerni\Spotify;

use Aerni\Spotify\Exceptions\ValidatorException;

class Spotify
{
    protected $defaultConfig;

    protected ?string $accessToken = null;

    public function __construct(array $defaultConfig)
    {
        $this->defaultConfig = $defaultConfig;
    }

    public function withToken(string $accessToken): self
    {
        $clone = clone $this;
        $clone->accessToken = $accessToken;

        return $clone;
    }

    public function album(string $id): PendingRequest
    {
        return new PendingRequest('/albums/'.$id, [
            'market' => $this->defaultConfig['market'],
        ], $this->accessToken);
    }

    public function albums($ids): PendingRequest
    {
        return new PendingRequest('/albums', [
            'ids' => $this->normalizeListToCsv($ids),
            'market' => $this->defaultConfig['market'],
        ], $this->accessToken);
    }

    public function albumTracks(string $id): PendingRequest
    {
        return new PendingRequest('/albums/'.$id.'/tracks/', [
            'limit' => null,
            'offset' => null,
            'market' => $this->defaultConfig['market'],
        ], $this->accessToken);
    }

    public function savedAlbums(): PendingRequest
    {
        return new PendingRequest('/me/albums', [
            'limit' => null,
            'offset' => null,
            'market' => $this->defaultConfig['market'],
        ], $this->accessToken);
    }

    public function saveAlbums($ids): PendingRequest
    {
        return $this->writeJsonRequest('PUT', '/me/albums', [
            'ids' => $this->normalizeListToArray($ids),
        ]);
    }

    public function removeAlbums($ids): PendingRequest
    {
        return $this->writeJsonRequest('DELETE', '/me/albums', [
            'ids' => $this->normalizeListToArray($ids),
        ]);
    }

    public function containsAlbums($ids): PendingRequest
    {
        return new PendingRequest('/me/albums/contains', [
            'ids' => $this->normalizeListToCsv($ids),
        ], $this->accessToken);
    }

    public function newReleases(): PendingRequest
    {
        return new PendingRequest('/browse/new-releases', [
            'country' => $this->defaultConfig['country'],
            'limit' => null,
            'offset' => null,
        ], $this->accessToken);
    }

    public function artist(string $id): PendingRequest
    {
        return new PendingRequest('/artists/'.$id, [], $this->accessToken);
    }

    public function artists($ids): PendingRequest
    {
        return new PendingRequest('/artists', [
            'ids' => $this->normalizeListToCsv($ids),
        ], $this->accessToken);
    }

    public function artistAlbums(string $id): PendingRequest
    {
        return new PendingRequest('/artists/'.$id.'/albums/', [
            'include_groups' => null,
            'country' => $this->defaultConfig['country'],
            'limit' => null,
            'offset' => null,
        ], $this->accessToken);
    }

    public function artistTopTracks(string $id): PendingRequest
    {
        return new PendingRequest('/artists/'.$id.'/top-tracks', [
            'market' => $this->defaultConfig['market'],
        ], $this->accessToken);
    }

    public function artistRelatedArtists(string $id): PendingRequest
    {
        return new PendingRequest('/artists/'.$id.'/related-artists', [], $this->accessToken);
    }

    public function audiobook(string $id): PendingRequest
    {
        return new PendingRequest('/audiobooks/'.$id, [
            'market' => $this->defaultConfig['market'],
        ], $this->accessToken);
    }

    public function audiobooks($ids): PendingRequest
    {
        return new PendingRequest('/audiobooks', [
            'ids' => $this->normalizeListToCsv($ids),
            'market' => $this->defaultConfig['market'],
        ], $this->accessToken);
    }

    public function audiobookChapters(string $id): PendingRequest
    {
        return new PendingRequest('/audiobooks/'.$id.'/chapters', [
            'limit' => null,
            'offset' => null,
            'market' => $this->defaultConfig['market'],
        ], $this->accessToken);
    }

    public function savedAudiobooks(): PendingRequest
    {
        return new PendingRequest('/me/audiobooks', [
            'limit' => null,
            'offset' => null,
        ], $this->accessToken);
    }

    public function saveAudiobooks($ids): PendingRequest
    {
        return $this->writeJsonRequest('PUT', '/me/audiobooks', [
            'ids' => $this->normalizeListToArray($ids),
        ]);
    }

    public function removeAudiobooks($ids): PendingRequest
    {
        return $this->writeJsonRequest('DELETE', '/me/audiobooks', [
            'ids' => $this->normalizeListToArray($ids),
        ]);
    }

    public function containsAudiobooks($ids): PendingRequest
    {
        return new PendingRequest('/me/audiobooks/contains', [
            'ids' => $this->normalizeListToCsv($ids),
        ], $this->accessToken);
    }

    public function categories(): PendingRequest
    {
        return new PendingRequest('/browse/categories', [
            'country' => $this->defaultConfig['country'],
            'locale' => $this->defaultConfig['locale'],
            'limit' => null,
            'offset' => null,
        ], $this->accessToken);
    }

    public function category(string $id): PendingRequest
    {
        return new PendingRequest('/browse/categories/'.$id, [
            'country' => $this->defaultConfig['country'],
            'locale' => $this->defaultConfig['locale'],
        ], $this->accessToken);
    }

    public function categoryPlaylists(string $id): PendingRequest
    {
        return new PendingRequest('/browse/categories/'.$id.'/playlists', [
            'country' => $this->defaultConfig['country'],
            'limit' => null,
            'offset' => null,
        ], $this->accessToken);
    }

    public function featuredPlaylists(): PendingRequest
    {
        return new PendingRequest('/browse/featured-playlists', [
            'country' => $this->defaultConfig['country'],
            'locale' => $this->defaultConfig['locale'],
            'timestamp' => null,
            'limit' => null,
            'offset' => null,
        ], $this->accessToken);
    }

    public function chapter(string $id): PendingRequest
    {
        return new PendingRequest('/chapters/'.$id, [
            'market' => $this->defaultConfig['market'],
        ], $this->accessToken);
    }

    public function chapters($ids): PendingRequest
    {
        return new PendingRequest('/chapters', [
            'ids' => $this->normalizeListToCsv($ids),
            'market' => $this->defaultConfig['market'],
        ], $this->accessToken);
    }

    public function episode(string $id): PendingRequest
    {
        return new PendingRequest('/episodes/'.$id, [
            'market' => $this->defaultConfig['market'],
        ], $this->accessToken);
    }

    public function availableMarkets(): PendingRequest
    {
        return new PendingRequest('/markets', [], $this->accessToken);
    }

    public function playbackState(): PendingRequest
    {
        return new PendingRequest('/me/player', [
            'market' => $this->defaultConfig['market'],
        ], $this->accessToken);
    }

    public function transferPlayback($deviceIds, bool $play = false): PendingRequest
    {
        return $this->writeJsonRequest('PUT', '/me/player', [
            'device_ids' => $this->normalizeListToArray($deviceIds),
            'play' => $play,
        ]);
    }

    public function availableDevices(): PendingRequest
    {
        return new PendingRequest('/me/player/devices', [], $this->accessToken);
    }

    public function currentlyPlayingTrack(): PendingRequest
    {
        return new PendingRequest('/me/player/currently-playing', [
            'market' => $this->defaultConfig['market'],
        ], $this->accessToken);
    }

    public function startOrResumePlayback(?string $deviceId = null, ?string $contextUri = null, ?int $positionMs = null, ?int $offsetPosition = null, ?string $offsetUri = null, ?array $uris = null): PendingRequest
    {
        $body = [];

        if ($contextUri !== null) {
            $body['context_uri'] = $contextUri;
        }

        if ($uris !== null) {
            $body['uris'] = $uris;
        }

        if ($offsetPosition !== null || $offsetUri !== null) {
            $body['offset'] = array_filter([
                'position' => $offsetPosition,
                'uri' => $offsetUri,
            ], fn ($value) => $value !== null);
        }

        if ($positionMs !== null) {
            $body['position_ms'] = $positionMs;
        }

        return $this->writeJsonRequest('PUT', $this->withQuery('/me/player/play', [
            'device_id' => $deviceId,
        ]), $body);
    }

    public function pausePlayback(?string $deviceId = null): PendingRequest
    {
        return $this->writeJsonRequest('PUT', $this->withQuery('/me/player/pause', [
            'device_id' => $deviceId,
        ]));
    }

    public function skipToNext(?string $deviceId = null): PendingRequest
    {
        return $this->writeJsonRequest('POST', $this->withQuery('/me/player/next', [
            'device_id' => $deviceId,
        ]));
    }

    public function skipToPrevious(?string $deviceId = null): PendingRequest
    {
        return $this->writeJsonRequest('POST', $this->withQuery('/me/player/previous', [
            'device_id' => $deviceId,
        ]));
    }

    public function seekToPosition(int $positionMs, ?string $deviceId = null): PendingRequest
    {
        return $this->writeJsonRequest('PUT', $this->withQuery('/me/player/seek', [
            'position_ms' => $positionMs,
            'device_id' => $deviceId,
        ]));
    }

    public function setRepeatMode(string $state, ?string $deviceId = null): PendingRequest
    {
        return $this->writeJsonRequest('PUT', $this->withQuery('/me/player/repeat', [
            'state' => $state,
            'device_id' => $deviceId,
        ]));
    }

    public function setPlaybackVolume(int $volumePercent, ?string $deviceId = null): PendingRequest
    {
        return $this->writeJsonRequest('PUT', $this->withQuery('/me/player/volume', [
            'volume_percent' => $volumePercent,
            'device_id' => $deviceId,
        ]));
    }

    public function togglePlaybackShuffle(bool $state, ?string $deviceId = null): PendingRequest
    {
        return $this->writeJsonRequest('PUT', $this->withQuery('/me/player/shuffle', [
            'state' => $state ? 'true' : 'false',
            'device_id' => $deviceId,
        ]));
    }

    public function playlist(string $id): PendingRequest
    {
        return new PendingRequest('/playlists/'.$id, [
            'fields' => null,
            'market' => $this->defaultConfig['market'],
        ], $this->accessToken);
    }

    public function changePlaylistDetails(string $id, ?string $name = null, ?bool $public = null, ?bool $collaborative = null, ?string $description = null): PendingRequest
    {
        return $this->writeJsonRequest('PUT', '/playlists/'.$id, array_filter([
            'name' => $name,
            'public' => $public,
            'collaborative' => $collaborative,
            'description' => $description,
        ], fn ($value) => $value !== null));
    }

    public function playlistItems(string $id): PendingRequest
    {
        return new PendingRequest('/playlists/'.$id.'/items/', [
            'fields' => null,
            'limit' => null,
            'offset' => null,
            'market' => $this->defaultConfig['market'],
        ], $this->accessToken);
    }

    public function currentUserPlaylists(): PendingRequest
    {
        return new PendingRequest('/me/playlists', [
            'limit' => null,
            'offset' => null,
        ], $this->accessToken);
    }

    public function userPlaylists(string $userId): PendingRequest
    {
        return new PendingRequest('/users/'.$userId.'/playlists', [
            'limit' => null,
            'offset' => null,
        ], $this->accessToken);
    }

    public function createPlaylist(string $userId, string $name, ?bool $public = null, ?bool $collaborative = null, ?string $description = null): PendingRequest
    {
        return $this->writeJsonRequest('POST', '/users/'.$userId.'/playlists', array_filter([
            'name' => $name,
            'public' => $public,
            'collaborative' => $collaborative,
            'description' => $description,
        ], fn ($value) => $value !== null));
    }

    public function addPlaylistItems(string $id, $uris, ?int $position = null): PendingRequest
    {
        $urisArray = is_array($uris) ? $uris : array_map('trim', explode(',', $uris));

        $body = ['uris' => $urisArray];
        if ($position !== null) {
            $body['position'] = $position;
        }

        return $this->writeJsonRequest('POST', '/playlists/'.$id.'/items', $body);
    }

    public function updatePlaylistItems(string $id, $uris, ?int $rangeStart = null, ?int $rangeLength = null, ?int $insertBefore = null): PendingRequest
    {
        $urisArray = is_array($uris) ? $uris : array_map('trim', explode(',', $uris));

        $body = ['uris' => $urisArray];
        if ($rangeStart !== null) {
            $body['range_start'] = $rangeStart;
        }
        if ($rangeLength !== null) {
            $body['range_length'] = $rangeLength;
        }
        if ($insertBefore !== null) {
            $body['insert_before'] = $insertBefore;
        }

        return $this->writeJsonRequest('PUT', '/playlists/'.$id.'/items', $body);
    }

    public function removePlaylistItems(string $id, $uris): PendingRequest
    {
        $urisArray = is_array($uris) ? $uris : array_map('trim', explode(',', $uris));

        return $this->writeJsonRequest('DELETE', '/playlists/'.$id.'/items', [
            'items' => array_map(fn ($uri) => ['uri' => $uri], $urisArray),
        ]);
    }

    public function playlistCoverImage(string $id): PendingRequest
    {
        return new PendingRequest('/playlists/'.$id.'/images/', [], $this->accessToken);
    }

    public function addCustomPlaylistCoverImage(string $id, string $base64JpegData): PendingRequest
    {
        $pending = new PendingRequest('/playlists/'.$id.'/images', [], $this->accessToken);
        $pending->method = 'PUT';
        $pending->rawBody = $base64JpegData;

        return $pending;
    }

    public function searchItems(string $query, $type): PendingRequest
    {
        return new PendingRequest('/search/', [
            'q' => $query,
            'type' => Validator::validateArgument('type', $type),
            'market' => $this->defaultConfig['market'],
            'limit' => null,
            'offset' => null,
            'include_external' => null,
        ], $this->accessToken);
    }

    public function searchAlbums(string $query): PendingRequest
    {
        return $this->searchType($query, 'album');
    }

    public function searchArtists(string $query): PendingRequest
    {
        return $this->searchType($query, 'artist');
    }

    public function searchAudiobooks(string $query): PendingRequest
    {
        return $this->searchType($query, 'audiobook');
    }

    public function searchPlaylists(string $query): PendingRequest
    {
        return $this->searchType($query, 'playlist');
    }

    public function searchTracks(string $query): PendingRequest
    {
        return $this->searchType($query, 'track');
    }

    public function show(string $id): PendingRequest
    {
        return new PendingRequest('/shows/'.$id, [
            'market' => $this->defaultConfig['market'],
        ], $this->accessToken);
    }

    public function showEpisodes(string $id): PendingRequest
    {
        return new PendingRequest('/shows/'.$id.'/episodes/', [
            'limit' => null,
            'offset' => null,
            'market' => $this->defaultConfig['market'],
        ], $this->accessToken);
    }

    public function track(string $id): PendingRequest
    {
        return new PendingRequest('/tracks/'.$id, [
            'market' => $this->defaultConfig['market'],
        ], $this->accessToken);
    }

    public function tracks($ids): PendingRequest
    {
        return new PendingRequest('/tracks', [
            'ids' => $this->normalizeListToCsv($ids),
            'market' => $this->defaultConfig['market'],
        ], $this->accessToken);
    }

    public function savedTracks(): PendingRequest
    {
        return new PendingRequest('/me/tracks', [
            'market' => $this->defaultConfig['market'],
            'limit' => null,
            'offset' => null,
        ], $this->accessToken);
    }

    public function saveTracks($ids): PendingRequest
    {
        return $this->writeJsonRequest('PUT', '/me/tracks', [
            'ids' => $this->normalizeListToArray($ids),
        ]);
    }

    public function removeTracks($ids): PendingRequest
    {
        return $this->writeJsonRequest('DELETE', '/me/tracks', [
            'ids' => $this->normalizeListToArray($ids),
        ]);
    }

    public function containsTracks($ids): PendingRequest
    {
        return new PendingRequest('/me/tracks/contains', [
            'ids' => $this->normalizeListToCsv($ids),
        ], $this->accessToken);
    }

    public function trackAudioFeatures(string $id): PendingRequest
    {
        return new PendingRequest('/audio-features/'.$id, [], $this->accessToken);
    }

    public function tracksAudioFeatures($ids): PendingRequest
    {
        return new PendingRequest('/audio-features', [
            'ids' => $this->normalizeListToCsv($ids),
        ], $this->accessToken);
    }

    public function trackAudioAnalysis(string $id): PendingRequest
    {
        return new PendingRequest('/audio-analysis/'.$id, [], $this->accessToken);
    }

    public function currentUserProfile(): PendingRequest
    {
        return new PendingRequest('/me', [], $this->accessToken);
    }

    public function userProfile(string $userId): PendingRequest
    {
        return new PendingRequest('/users/'.$userId, [], $this->accessToken);
    }

    public function followArtistsOrUsers($ids, string $type): PendingRequest
    {
        $this->assertOneOf('type', $type, ['artist', 'user']);

        return $this->writeJsonRequest('PUT', $this->withQuery('/me/following', [
            'type' => $type,
            'ids' => $this->normalizeListToCsv($ids),
        ]));
    }

    public function unfollowArtistsOrUsers($ids, string $type): PendingRequest
    {
        $this->assertOneOf('type', $type, ['artist', 'user']);

        return $this->writeJsonRequest('DELETE', $this->withQuery('/me/following', [
            'type' => $type,
            'ids' => $this->normalizeListToCsv($ids),
        ]));
    }

    public function currentUserFollowsArtistsOrUsers($ids, string $type): PendingRequest
    {
        $this->assertOneOf('type', $type, ['artist', 'user']);

        return new PendingRequest('/me/following/contains', [
            'type' => $type,
            'ids' => $this->normalizeListToCsv($ids),
        ], $this->accessToken);
    }

    public function followedArtists(): PendingRequest
    {
        return new PendingRequest('/me/following', [
            'type' => 'artist',
            'after' => null,
            'limit' => null,
        ], $this->accessToken);
    }

    public function libraryContains($uris): PendingRequest
    {
        $this->removedEndpoint('libraryContains', 'Spotify removed generic /me/library endpoints. Use track/album/audiobook specific saved-item endpoints.');
    }

    public function saveToLibrary($uris): PendingRequest
    {
        $this->removedEndpoint('saveToLibrary', 'Spotify removed generic /me/library endpoints. Use saveTracks/saveAlbums/saveAudiobooks.');
    }

    public function removeFromLibrary($uris): PendingRequest
    {
        $this->removedEndpoint('removeFromLibrary', 'Spotify removed generic /me/library endpoints. Use removeTracks/removeAlbums/removeAudiobooks.');
    }

    public function recommendations($seedArtists = null, $seedGenres = null, $seedTracks = null): PendingRequest
    {
        $this->removedEndpoint('recommendations', 'Removed by Spotify in February 2026.');
    }

    public function availableGenreSeeds(): PendingRequest
    {
        $this->removedEndpoint('availableGenreSeeds', 'Removed by Spotify in February 2026.');
    }

    public function episodes($ids): PendingRequest
    {
        $this->removedEndpoint('episodes', 'Get Several Episodes was removed by Spotify in February 2026.');
    }

    public function savedEpisodes(): PendingRequest
    {
        $this->removedEndpoint('savedEpisodes', 'Get Users Saved Episodes was removed by Spotify in February 2026.');
    }

    public function saveEpisodes($ids): PendingRequest
    {
        $this->removedEndpoint('saveEpisodes', 'Save Episodes for Current User was removed by Spotify in February 2026.');
    }

    public function removeEpisodes($ids): PendingRequest
    {
        $this->removedEndpoint('removeEpisodes', 'Remove Users Saved Episodes was removed by Spotify in February 2026.');
    }

    public function containsEpisodes($ids): PendingRequest
    {
        $this->removedEndpoint('containsEpisodes', 'Check Users Saved Episodes was removed by Spotify in February 2026.');
    }

    public function recentlyPlayedTracks(): PendingRequest
    {
        $this->removedEndpoint('recentlyPlayedTracks', 'Get Recently Played Tracks was removed by Spotify in February 2026.');
    }

    public function queue(): PendingRequest
    {
        $this->removedEndpoint('queue', 'Get Queue was removed by Spotify in February 2026.');
    }

    public function addItemToPlaybackQueue(string $uri, ?string $deviceId = null): PendingRequest
    {
        $this->removedEndpoint('addItemToPlaybackQueue', 'Add Item to Playback Queue was removed by Spotify in February 2026.');
    }

    public function followPlaylist(string $playlistId, bool $public = true): PendingRequest
    {
        $this->removedEndpoint('followPlaylist', 'Follow Playlist was removed by Spotify in February 2026.');
    }

    public function unfollowPlaylist(string $playlistId): PendingRequest
    {
        $this->removedEndpoint('unfollowPlaylist', 'Unfollow Playlist was removed by Spotify in February 2026.');
    }

    public function currentUserFollowsPlaylist(string $playlistId, $ids): PendingRequest
    {
        $this->removedEndpoint('currentUserFollowsPlaylist', 'Check if Current User Follows Playlist was removed by Spotify in February 2026.');
    }

    public function searchEpisodes(string $query): PendingRequest
    {
        $this->removedEndpoint('searchEpisodes', 'Search for Item type=episode was removed by Spotify in February 2026.');
    }

    public function searchShows(string $query): PendingRequest
    {
        $this->removedEndpoint('searchShows', 'Search for Item type=show was removed by Spotify in February 2026.');
    }

    public function shows($ids): PendingRequest
    {
        $this->removedEndpoint('shows', 'Get Several Shows was removed by Spotify in February 2026.');
    }

    public function savedShows(): PendingRequest
    {
        $this->removedEndpoint('savedShows', 'Get Users Saved Shows was removed by Spotify in February 2026.');
    }

    public function saveShows($ids): PendingRequest
    {
        $this->removedEndpoint('saveShows', 'Save Shows for Current User was removed by Spotify in February 2026.');
    }

    public function removeShows($ids): PendingRequest
    {
        $this->removedEndpoint('removeShows', 'Remove Users Saved Shows was removed by Spotify in February 2026.');
    }

    public function containsShows($ids): PendingRequest
    {
        $this->removedEndpoint('containsShows', 'Check Users Saved Shows was removed by Spotify in February 2026.');
    }

    public function currentUserTopItems(string $type): PendingRequest
    {
        $this->removedEndpoint('currentUserTopItems', 'Get Users Top Items was removed by Spotify in February 2026.');
    }

    private function searchType(string $query, string $type): PendingRequest
    {
        return new PendingRequest('/search/', [
            'q' => $query,
            'type' => $type,
            'market' => $this->defaultConfig['market'],
            'limit' => null,
            'offset' => null,
            'include_external' => null,
        ], $this->accessToken);
    }

    private function normalizeListToCsv($values): string
    {
        return Validator::validateArgument('values', $values);
    }

    private function normalizeListToArray($values): array
    {
        if (is_array($values) && $values !== []) {
            return $values;
        }

        if (is_string($values) && trim($values) !== '') {
            return array_values(array_filter(array_map('trim', explode(',', $values)), fn ($value) => $value !== ''));
        }

        throw new ValidatorException('Please provide a string with comma-separated values or an array as the argument to the [values] parameter.');
    }

    private function assertOneOf(string $name, string $value, array $allowed): void
    {
        if (! in_array($value, $allowed, true)) {
            throw new ValidatorException('Invalid ['.$name.'] value ['.$value.']. Allowed values: ['.implode(', ', $allowed).'].');
        }
    }

    private function withQuery(string $endpoint, array $params): string
    {
        $filtered = array_filter($params, fn ($value) => $value !== null);

        if ($filtered === []) {
            return $endpoint;
        }

        return $endpoint.'?'.http_build_query($filtered);
    }

    private function writeJsonRequest(string $method, string $endpoint, array $body = []): PendingRequest
    {
        $pending = new PendingRequest($endpoint, [], $this->accessToken);
        $pending->method = $method;
        $pending->body = $body;

        return $pending;
    }

    private function removedEndpoint(string $method, string $details): never
    {
        throw new ValidatorException('The ['.$method.'] endpoint is no longer available. '.$details.' See https://developer.spotify.com/documentation/web-api/references/changes/february-2026');
    }
}




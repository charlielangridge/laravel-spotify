<?php

namespace Aerni\Spotify;

class Spotify
{
    protected $defaultConfig;

    protected ?string $accessToken = null;

    public function __construct(array $defaultConfig)
    {
        $this->defaultConfig = $defaultConfig;
    }

    /**
     * Set a user access token for Authorization Code Flow requests.
     */
    public function withToken(string $accessToken): self
    {
        $clone = clone $this;
        $clone->accessToken = $accessToken;

        return $clone;
    }

    /**
     * Get Spotify catalog information for a single album.
     */
    public function album(string $id): PendingRequest
    {
        $endpoint = '/albums/'.$id;

        $acceptedParams = [
            'market' => $this->defaultConfig['market'],
        ];

        return new PendingRequest($endpoint, $acceptedParams, $this->accessToken);
    }

    /**
     * Get Spotify catalog information about an album's tracks. Optional parameters can be used to limit the number of tracks returned.
     */
    public function albumTracks(string $id): PendingRequest
    {
        $endpoint = '/albums/'.$id.'/tracks/';

        $acceptedParams = [
            'limit' => null,
            'offset' => null,
            'market' => $this->defaultConfig['market'],
        ];

        return new PendingRequest($endpoint, $acceptedParams, $this->accessToken);
    }

    /**
     * Get Spotify catalog information for a single artist identified by their unique Spotify ID.
     */
    public function artist(string $id): PendingRequest
    {
        $endpoint = '/artists/'.$id;

        return new PendingRequest($endpoint, [], $this->accessToken);
    }

    /**
     * Get Spotify catalog information about an artist's albums. Optional parameters can be specified in the query string to filter and sort the response.
     */
    public function artistAlbums(string $id): PendingRequest
    {
        $endpoint = '/artists/'.$id.'/albums/';

        $acceptedParams = [
            'include_groups' => null,
            'country' => $this->defaultConfig['country'],
            'limit' => null,
            'offset' => null,
        ];

        return new PendingRequest($endpoint, $acceptedParams, $this->accessToken);
    }

    /**
     * Get Spotify catalog information for a single episode identified by its unique Spotify ID.
     */
    public function episode(string $id): PendingRequest
    {
        $endpoint = '/episodes/'.$id;

        $acceptedParams = [
            'market' => $this->defaultConfig['market'],
        ];

        return new PendingRequest($endpoint, $acceptedParams, $this->accessToken);
    }

    /**
     * Check whether one or more items are present in the current user's library.
     * Requires access token with scope: user-library-read.
     *
     * @param  array|string  $uris  Spotify URIs (comma-separated string or array)
     */
    public function libraryContains($uris): PendingRequest
    {
        $urisString = is_array($uris) ? implode(',', $uris) : $uris;

        $acceptedParams = [
            'uris' => $urisString,
        ];

        return new PendingRequest('/me/library/contains', $acceptedParams, $this->accessToken);
    }

    /**
     * Save one or more items to the current user's library.
     * Requires access token with scope: user-library-modify.
     *
     * @param  array|string  $uris  Spotify URIs (comma-separated string or array)
     */
    public function saveToLibrary($uris): PendingRequest
    {
        $urisArray = is_array($uris) ? $uris : array_map('trim', explode(',', $uris));

        $pending = new PendingRequest('/me/library', [], $this->accessToken);
        $pending->method = 'PUT';
        $pending->body = ['uris' => $urisArray];

        return $pending;
    }

    /**
     * Remove one or more items from the current user's library.
     * Requires access token with scope: user-library-modify.
     *
     * @param  array|string  $uris  Spotify URIs (comma-separated string or array)
     */
    public function removeFromLibrary($uris): PendingRequest
    {
        $urisArray = is_array($uris) ? $uris : array_map('trim', explode(',', $uris));

        $pending = new PendingRequest('/me/library', [], $this->accessToken);
        $pending->method = 'DELETE';
        $pending->body = ['uris' => $urisArray];

        return $pending;
    }

    /**
     * Get the current image associated with a specific playlist.
     */
    public function playlistCoverImage(string $id): PendingRequest
    {
        $endpoint = '/playlists/'.$id.'/images/';

        return new PendingRequest($endpoint, [], $this->accessToken);
    }

    /**
     * Get a playlist owned by a Spotify user.
     */
    public function playlist(string $id): PendingRequest
    {
        $endpoint = '/playlists/'.$id;

        $acceptedParams = [
            'fields' => null,
            'market' => $this->defaultConfig['market'],
        ];

        return new PendingRequest($endpoint, $acceptedParams, $this->accessToken);
    }

    /**
     * Get full details of the items of a playlist owned by a Spotify user.
     */
    public function playlistItems(string $id): PendingRequest
    {
        $endpoint = '/playlists/'.$id.'/items/';

        $acceptedParams = [
            'fields' => null,
            'limit' => null,
            'offset' => null,
            'market' => $this->defaultConfig['market'],
        ];

        return new PendingRequest($endpoint, $acceptedParams, $this->accessToken);
    }

    /**
     * Add one or more items to a playlist.
     * Requires access token with scope: playlist-modify-public or playlist-modify-private.
     *
     * @param  array|string  $uris  Spotify URIs (comma-separated string or array)
     */
    public function addPlaylistItems(string $id, $uris, ?int $position = null): PendingRequest
    {
        $urisArray = is_array($uris) ? $uris : array_map('trim', explode(',', $uris));

        $body = ['uris' => $urisArray];
        if ($position !== null) {
            $body['position'] = $position;
        }

        $pending = new PendingRequest('/playlists/'.$id.'/items', [], $this->accessToken);
        $pending->method = 'POST';
        $pending->body = $body;

        return $pending;
    }

    /**
     * Reorder or replace items in a playlist.
     * Requires access token with scope: playlist-modify-public or playlist-modify-private.
     *
     * @param  array|string  $uris  Spotify URIs (comma-separated string or array)
     */
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

        $pending = new PendingRequest('/playlists/'.$id.'/items', [], $this->accessToken);
        $pending->method = 'PUT';
        $pending->body = $body;

        return $pending;
    }

    /**
     * Remove one or more items from a playlist.
     * Requires access token with scope: playlist-modify-public or playlist-modify-private.
     *
     * @param  array|string  $uris  Spotify URIs (comma-separated string or array)
     */
    public function removePlaylistItems(string $id, $uris): PendingRequest
    {
        $urisArray = is_array($uris) ? $uris : array_map('trim', explode(',', $uris));

        $pending = new PendingRequest('/playlists/'.$id.'/items', [], $this->accessToken);
        $pending->method = 'DELETE';
        $pending->body = ['items' => array_map(fn ($uri) => ['uri' => $uri], $urisArray)];

        return $pending;
    }

    /**
     * Get Spotify Catalog information about artists, albums, tracks or playlists that match a keyword string.
     * The limit parameter has a maximum of 10 and a default of 5.
     *
     * @param  array|string  $type
     */
    public function searchItems(string $query, $type): PendingRequest
    {
        $endpoint = '/search/';

        $acceptedParams = [
            'q' => $query,
            'type' => Validator::validateArgument('type', $type),
            'market' => $this->defaultConfig['market'],
            'limit' => null,
            'offset' => null,
            'include_external' => null,
        ];

        return new PendingRequest($endpoint, $acceptedParams, $this->accessToken);
    }

    /**
     * Get Spotify Catalog information about albums that match a keyword string.
     * The limit parameter has a maximum of 10 and a default of 5.
     */
    public function searchAlbums(string $query): PendingRequest
    {
        $endpoint = '/search/';

        $acceptedParams = [
            'q' => $query,
            'type' => 'album',
            'market' => $this->defaultConfig['market'],
            'limit' => null,
            'offset' => null,
            'include_external' => null,
        ];

        return new PendingRequest($endpoint, $acceptedParams, $this->accessToken);
    }

    /**
     * Get Spotify Catalog information about artists that match a keyword string.
     * The limit parameter has a maximum of 10 and a default of 5.
     */
    public function searchArtists(string $query): PendingRequest
    {
        $endpoint = '/search/';

        $acceptedParams = [
            'q' => $query,
            'type' => 'artist',
            'market' => $this->defaultConfig['market'],
            'limit' => null,
            'offset' => null,
            'include_external' => null,
        ];

        return new PendingRequest($endpoint, $acceptedParams, $this->accessToken);
    }

    /**
     * Get Spotify Catalog information about episodes that match a keyword string.
     * The limit parameter has a maximum of 10 and a default of 5.
     */
    public function searchEpisodes(string $query): PendingRequest
    {
        $endpoint = '/search/';

        $acceptedParams = [
            'q' => $query,
            'type' => 'episode',
            'market' => $this->defaultConfig['market'],
            'limit' => null,
            'offset' => null,
            'include_external' => null,
        ];

        return new PendingRequest($endpoint, $acceptedParams, $this->accessToken);
    }

    /**
     * Get Spotify Catalog information about playlists that match a keyword string.
     * The limit parameter has a maximum of 10 and a default of 5.
     */
    public function searchPlaylists(string $query): PendingRequest
    {
        $endpoint = '/search/';

        $acceptedParams = [
            'q' => $query,
            'type' => 'playlist',
            'market' => $this->defaultConfig['market'],
            'limit' => null,
            'offset' => null,
            'include_external' => null,
        ];

        return new PendingRequest($endpoint, $acceptedParams, $this->accessToken);
    }

    /**
     * Get Spotify Catalog information about shows that match a keyword string.
     * The limit parameter has a maximum of 10 and a default of 5.
     */
    public function searchShows(string $query): PendingRequest
    {
        $endpoint = '/search/';

        $acceptedParams = [
            'q' => $query,
            'type' => 'show',
            'market' => $this->defaultConfig['market'],
            'limit' => null,
            'offset' => null,
            'include_external' => null,
        ];

        return new PendingRequest($endpoint, $acceptedParams, $this->accessToken);
    }

    /**
     * Get Spotify Catalog information about tracks that match a keyword string.
     * The limit parameter has a maximum of 10 and a default of 5.
     */
    public function searchTracks(string $query): PendingRequest
    {
        $endpoint = '/search/';

        $acceptedParams = [
            'q' => $query,
            'type' => 'track',
            'market' => $this->defaultConfig['market'],
            'limit' => null,
            'offset' => null,
            'include_external' => null,
        ];

        return new PendingRequest($endpoint, $acceptedParams, $this->accessToken);
    }

    /**
     * Get Spotify catalog information for a single show identified by its unique Spotify ID.
     */
    public function show(string $id): PendingRequest
    {
        $endpoint = '/shows/'.$id;

        $acceptedParams = [
            'market' => $this->defaultConfig['market'],
        ];

        return new PendingRequest($endpoint, $acceptedParams, $this->accessToken);
    }

    /**
     * Get Spotify catalog information about a show's episodes.
     */
    public function showEpisodes(string $id): PendingRequest
    {
        $endpoint = '/shows/'.$id.'/episodes/';

        $acceptedParams = [
            'limit' => null,
            'offset' => null,
            'market' => $this->defaultConfig['market'],
        ];

        return new PendingRequest($endpoint, $acceptedParams, $this->accessToken);
    }

    /**
     * Get Spotify catalog information for a single track identified by its unique Spotify ID.
     */
    public function track(string $id): PendingRequest
    {
        $endpoint = '/tracks/'.$id;

        $acceptedParams = [
            'market' => $this->defaultConfig['market'],
        ];

        return new PendingRequest($endpoint, $acceptedParams, $this->accessToken);
    }
}

<h1 align="center">
    <img src="https://github.com/aerni/laravel-spotify/blob/master/logo.png" width="160">
    <br>
    Spotify for Laravel
    <br>
</h1>
<h4 align="center">An easy to use Spotify Web API wrapper for Laravel</h4>
<p align="center">
    <a href="https://packagist.org/packages/aerni/laravel-spotify">
        <img src="https://flat.badgen.net/packagist/v/aerni/laravel-spotify" alt="Packagist version">
    </a>
    <a href="https://packagist.org/packages/aerni/laravel-spotify">
        <img src="https://flat.badgen.net/packagist/dt/aerni/laravel-spotify" alt="Packagist total downloads">
    </a>
    <a href="https://github.com/aerni/laravel-spotify/blob/master/LICENSE">
        <img src="https://flat.badgen.net/github/license/aerni/laravel-spotify" alt="GitHub license">
    </a>
    <a href="https://www.paypal.me/michaelaerni">
        <img src="https://img.shields.io/badge/PayPal-donate-blue.svg?style=flat-square" alt="PayPal donate">
    </a>
</p>
<p align="center">
    <a href="#installation">Installation</a> •
    <a href="#authorization-code-flow">Authorization Code Flow</a> •
    <a href="#usage-example">Usage Example</a> •
    <a href="#optional-parameters">Optional Parameters</a> •
    <a href="#spotify-api-reference">Spotify API Reference</a> •
    <br>
    <br>
</p>

## Introduction
Spotify for Laravel makes working with the Spotify Web API a breeze. It provides straight forward methods for each endpoint and a fluent interface for optional parameters.

The package supports Spotify Web API endpoints that are currently available in the official reference for the [Client Credentials Flow](https://developer.spotify.com/documentation/general/guides/authorization-guide/#client-credentials-flow) (public data) and the [Authorization Code Flow](https://developer.spotify.com/documentation/general/guides/authorization-guide/#authorization-code-flow) (user-specific data such as saved tracks, private playlists, and user profile).

## Installation
Install the package using Composer. The package will automatically register itself.

```bash
composer require aerni/laravel-spotify
```

Publish the config of the package.

```bash
php artisan vendor:publish --provider="Aerni\Spotify\SpotifyServiceProvider"
```

The following config will be published to `config/spotify.php`.

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    |
    | The Client ID, Client Secret, and Redirect URI of your Spotify App.
    |
    */

    'auth' => [
        'client_id'     => env('SPOTIFY_CLIENT_ID'),
        'client_secret' => env('SPOTIFY_CLIENT_SECRET'),
        'redirect_uri'  => env('SPOTIFY_REDIRECT_URI'),  // Required for Authorization Code Flow
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Repository
    |--------------------------------------------------------------------------
    |
    | Used by the Authorization Code Flow to store user tokens. The default
    | DatabaseTokenRepository persists tokens across server restarts.
    | Switch to CacheTokenRepository if you prefer not to run a migration.
    |
    */

    'token_repository' => \Aerni\Spotify\Repositories\DatabaseTokenRepository::class,

    /*
    |--------------------------------------------------------------------------
    | Default Config
    |--------------------------------------------------------------------------
    |
    | You may define a default country, locale and market that will be used
    | for your Spotify API requests.
    |
    */

    'default_config' => [
        'country' => null,
        'locale' => null,
        'market' => null,
    ],

];
```

Set the `Client ID` and `Client Secret` of your [Spotify App](https://developer.spotify.com/dashboard) in your `.env` file.

```env
SPOTIFY_CLIENT_ID=********************************
SPOTIFY_CLIENT_SECRET=********************************
SPOTIFY_REDIRECT_URI=https://your-app.com/spotify/callback
```

## Authorization Code Flow

### Overview

Use the Authorization Code Flow when your app needs access to user-specific data — saved tracks, private playlists, the current user's profile, etc. Unlike the Client Credentials Flow (server-to-server only), this flow authenticates on behalf of a real Spotify user.

### Configuration

**1. Run the migration** to create the `spotify_tokens` table:

```bash
php artisan vendor:publish --provider="Aerni\Spotify\SpotifyServiceProvider" --tag=migrations
php artisan migrate
```

**2. Set your redirect URI** in `.env`:

```env
SPOTIFY_REDIRECT_URI=https://your-app.com/spotify/callback
```

**3. Register the redirect URI** in your [Spotify Developer Dashboard](https://developer.spotify.com/dashboard) under your app's settings.

### Step 1: Redirect the User

```php
use Aerni\Spotify\Facades\SpotifyAuthorizationCode;
use Illuminate\Support\Str;

$state = Str::random(16);
session(['spotify_state' => $state]);

return redirect(SpotifyAuthorizationCode::getAuthorizationUrl(
    scopes: ['user-read-email', 'playlist-read-private'],
    state: $state,
));
```

### Step 2: Handle the Callback

```php
use Aerni\Spotify\Facades\SpotifyAuthorizationCode;

// Validate state to prevent CSRF
abort_unless(request('state') === session('spotify_state'), 403);

// Exchange the code for tokens — stored automatically in the token repository
SpotifyAuthorizationCode::exchangeCodeForTokens(auth()->id(), request('code'));
```

### Step 3: Make User-Authenticated API Calls

```php
use Aerni\Spotify\Facades\Spotify;
use Aerni\Spotify\Facades\SpotifyAuthorizationCode;

// Retrieves a valid token, refreshing automatically if expired
$token = SpotifyAuthorizationCode::getAccessTokenForUser(auth()->id());

$results = Spotify::withToken($token)->searchTracks('Adele')->limit(10)->get();
```

### Logout / Token Revocation

```php
SpotifyAuthorizationCode::forgetTokens(auth()->id());
```

### Token Management Reference

| Method | Description |
|--------|-------------|
| `getAuthorizationUrl(array $scopes, ?string $state)` | Generate the Spotify login URL to redirect the user to |
| `exchangeCodeForTokens($userId, string $code)` | Exchange the callback code for tokens; stores them automatically |
| `getAccessTokenForUser($userId)` | Return a valid access token, auto-refreshing if expired |
| `refreshAccessToken($userId)` | Manually refresh the access token using the stored refresh token |
| `forgetTokens($userId)` | Delete stored tokens for the user (logout) |

### Swapping the Token Repository

By default, tokens are stored in a database table (`spotify_tokens`) and survive server restarts. If you prefer not to run a migration, switch to the cache-based repository in `config/spotify.php`:

```php
'token_repository' => \Aerni\Spotify\Repositories\CacheTokenRepository::class,
```

> **Note:** The `CacheTokenRepository` is not durable — tokens are lost when the cache is cleared or the server restarts, requiring users to re-authenticate.

---

## Usage Example
Import the package at the top of your file. All of the following examples use the [Facade](https://laravel.com/docs/master/facades).

```php
use Aerni\Spotify\Facades\Spotify;
```

Search for tracks with the name `Closed on Sunday`.

```php
Spotify::searchTracks('Closed on Sunday')->get();
```

**Important:** The `get()` method acts as the final method of the fluent interface. Make sure to always call it last in the method chain to execute a request to the Spotify Web API.

## Optional Parameters
You may pass optional parameters to your requests using the fluent interface provided by this package. A common use case is to set a `limit` and `offset` to your request.

```php
Spotify::searchTracks('Closed on Sunday')->limit(5)->offset(5)->get();
```

### Parameter Methods API Reference
Consult the [Spotify Web API Reference Documentation](https://developer.spotify.com/documentation/web-api/reference/) to check which parameters are available to what endpoint.

```php
// Limit the response to a particular geographical market.
Spotify::artistAlbums('artist_id')->country('US')->get();

// Filter the query using the provided string.
Spotify::playlist('playlist_id')->fields('description, uri')->get();

// Include any relevant content that is hosted externally.
Spotify::searchTracks('query')->includeExternal('audio')->get();

// Filter the response using the provided string.
Spotify::artistAlbums('artist_id')->includeGroups('album, single, appears_on, compilation')->get();

// Set the number of objects to be returned (max 10, default 5 for search endpoints).
Spotify::searchTracks('query')->limit(10)->get();

// Set the index of the first object to be returned.
Spotify::searchTracks('query')->offset(10)->get();

// Limit the response to a particular geographical market.
Spotify::searchAlbums('query')->market('US')->get();
```

### Resetting Defaults
You may want to reset the default setting of `country`, `locale` or `market` for a given request. You may do so by calling the corresponding parameter method with an empty argument.

```php
// This will reset the default market to nothing.
Spotify::searchTracks('query')->market()->get();
```

### Response Key
Some API responses are wrapped in a top level object like `artists` or `tracks`. If you want to directly access the content of a given top level object, you may do so by passing its key as a string to the `get()` method.

```php
// This will return the content of the tracks object.
Spotify::searchTracks('query')->get('tracks');
```

## Spotify API Reference

This section reflects Spotify's current Web API availability, including the February 2026 changes:
https://developer.spotify.com/documentation/web-api/references/changes/february-2026

**Note:** Any parameter that accepts multiple values can receive either a comma-separated string or an array.

### Albums

```php
Spotify::album('album_id')->get();
Spotify::albums(['id1', 'id2'])->get();
Spotify::albumTracks('album_id')->get();
Spotify::withToken($token)->savedAlbums()->limit(20)->offset(0)->get();
Spotify::withToken($token)->saveAlbums(['id1', 'id2'])->get();
Spotify::withToken($token)->removeAlbums(['id1', 'id2'])->get();
Spotify::withToken($token)->containsAlbums(['id1', 'id2'])->get();
Spotify::newReleases()->country('US')->limit(20)->get();
```

### Artists

```php
Spotify::artist('artist_id')->get();
Spotify::artists(['id1', 'id2'])->get();
Spotify::artistAlbums('artist_id')->get();
Spotify::artistTopTracks('artist_id')->market('US')->get();
Spotify::artistRelatedArtists('artist_id')->get();
```

### Audiobooks

```php
Spotify::audiobook('audiobook_id')->get();
Spotify::audiobooks(['id1', 'id2'])->get();
Spotify::audiobookChapters('audiobook_id')->limit(20)->offset(0)->get();
Spotify::withToken($token)->savedAudiobooks()->limit(20)->offset(0)->get();
Spotify::withToken($token)->saveAudiobooks(['id1', 'id2'])->get();
Spotify::withToken($token)->removeAudiobooks(['id1', 'id2'])->get();
Spotify::withToken($token)->containsAudiobooks(['id1', 'id2'])->get();
```

### Browse

```php
Spotify::categories()->country('US')->locale('en_US')->limit(20)->get();
Spotify::category('category_id')->country('US')->locale('en_US')->get();
Spotify::categoryPlaylists('category_id')->country('US')->limit(20)->get();
Spotify::featuredPlaylists()->country('US')->locale('en_US')->timestamp(now()->toIso8601String())->get();
```

### Chapters And Episodes

```php
Spotify::chapter('chapter_id')->get();
Spotify::chapters(['id1', 'id2'])->get();
Spotify::episode('episode_id')->get();
```

### Markets

```php
Spotify::availableMarkets()->get();
```

### Player

```php
Spotify::withToken($token)->playbackState()->get();
Spotify::withToken($token)->transferPlayback(['device_id'], play: true)->get();
Spotify::withToken($token)->availableDevices()->get();
Spotify::withToken($token)->currentlyPlayingTrack()->get();
Spotify::withToken($token)->startOrResumePlayback(deviceId: 'device_id', contextUri: 'spotify:album:...')->get();
Spotify::withToken($token)->pausePlayback(deviceId: 'device_id')->get();
Spotify::withToken($token)->skipToNext(deviceId: 'device_id')->get();
Spotify::withToken($token)->skipToPrevious(deviceId: 'device_id')->get();
Spotify::withToken($token)->seekToPosition(30000, deviceId: 'device_id')->get();
Spotify::withToken($token)->setRepeatMode('track', deviceId: 'device_id')->get();
Spotify::withToken($token)->setPlaybackVolume(75, deviceId: 'device_id')->get();
Spotify::withToken($token)->togglePlaybackShuffle(true, deviceId: 'device_id')->get();
```

### Playlists

```php
Spotify::playlist('playlist_id')->get();
Spotify::playlistItems('playlist_id')->get();
Spotify::playlistCoverImage('playlist_id')->get();
Spotify::withToken($token)->changePlaylistDetails('playlist_id', name: 'New name', description: 'Updated')->get();
Spotify::withToken($token)->currentUserPlaylists()->limit(20)->offset(0)->get();
Spotify::userPlaylists('user_id')->limit(20)->offset(0)->get();
Spotify::withToken($token)->createPlaylist('user_id', 'My Playlist', public: false)->get();
Spotify::withToken($token)->addPlaylistItems('playlist_id', ['spotify:track:id1'])->get();
Spotify::withToken($token)->updatePlaylistItems('playlist_id', ['spotify:track:id1'])->get();
Spotify::withToken($token)->removePlaylistItems('playlist_id', ['spotify:track:id1'])->get();
Spotify::withToken($token)->addCustomPlaylistCoverImage('playlist_id', $base64Jpeg)->get();
```

### Search

```php
Spotify::searchItems('query', 'album,artist,audiobook,playlist,track')->get();
Spotify::searchAlbums('query')->get();
Spotify::searchArtists('query')->get();
Spotify::searchAudiobooks('query')->get();
Spotify::searchPlaylists('query')->get();
Spotify::searchTracks('query')->get();
```

### Shows

```php
Spotify::show('show_id')->get();
Spotify::showEpisodes('show_id')->get();
```

### Tracks

```php
Spotify::track('track_id')->get();
Spotify::tracks(['id1', 'id2'])->get();
Spotify::withToken($token)->savedTracks()->limit(20)->offset(0)->get();
Spotify::withToken($token)->saveTracks(['id1', 'id2'])->get();
Spotify::withToken($token)->removeTracks(['id1', 'id2'])->get();
Spotify::withToken($token)->containsTracks(['id1', 'id2'])->get();
Spotify::trackAudioFeatures('track_id')->get();
Spotify::tracksAudioFeatures(['id1', 'id2'])->get();
Spotify::trackAudioAnalysis('track_id')->get();
```

### Users And Following

```php
Spotify::withToken($token)->currentUserProfile()->get();
Spotify::userProfile('user_id')->get();
Spotify::withToken($token)->followArtistsOrUsers(['id1'], 'artist')->get();
Spotify::withToken($token)->unfollowArtistsOrUsers(['id1'], 'artist')->get();
Spotify::withToken($token)->currentUserFollowsArtistsOrUsers(['id1'], 'artist')->get();
Spotify::withToken($token)->followedArtists()->limit(20)->after('artist_id')->get();
```

### Removed Endpoints (February 2026)

Calling any removed endpoint method now throws a `ValidatorException` with a migration message and changelog URL.

Removed methods include:
`libraryContains`, `saveToLibrary`, `removeFromLibrary`, `recommendations`, `availableGenreSeeds`,
`episodes`, `savedEpisodes`, `saveEpisodes`, `removeEpisodes`, `containsEpisodes`,
`recentlyPlayedTracks`, `queue`, `addItemToPlaybackQueue`,
`followPlaylist`, `unfollowPlaylist`, `currentUserFollowsPlaylist`,
`searchEpisodes`, `searchShows`,
`shows`, `savedShows`, `saveShows`, `removeShows`, `containsShows`,
`currentUserTopItems`.

## Tests
Run the tests like this:

```bash
vendor/bin/phpunit
```

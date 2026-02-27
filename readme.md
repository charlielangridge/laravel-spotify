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

The package supports all Spotify Web API endpoints accessible with the [Client Credentials Flow](https://developer.spotify.com/documentation/general/guides/authorization-guide/#client-credentials-flow) (public data) and the [Authorization Code Flow](https://developer.spotify.com/documentation/general/guides/authorization-guide/#authorization-code-flow) (user-specific data such as saved tracks, private playlists, and user profile).

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

**Note:** Any parameter that accepts multiple values can either receive a string with comma-separated values or an array of values.

```php
// Pass a string with comma-separated values
Spotify::libraryContains('spotify:track:id1, spotify:track:id2')->get();

// Or pass an array of values
Spotify::libraryContains(['spotify:track:id1', 'spotify:track:id2'])->get();
```

### Albums

```php
// Get an album by ID.
Spotify::album('album_id')->get();

// Get the tracks of an album by ID.
Spotify::albumTracks('album_id')->get();
```

### Artists

```php
// Get an artist by ID.
Spotify::artist('artist_id')->get();

// Get albums of an artist by ID.
Spotify::artistAlbums('artist_id')->get();
```

### Episodes

```php
// Get an episode by ID.
Spotify::episode('episode_id')->get();
```

### Library

Library endpoints require a user access token with the appropriate scope. Use `withToken()` to provide the token.

```php
// Check if one or more items are in the current user's library. Requires scope: user-library-read.
Spotify::withToken($token)->libraryContains(['spotify:track:id1', 'spotify:track:id2'])->get();

// Save one or more items to the current user's library. Requires scope: user-library-modify.
Spotify::withToken($token)->saveToLibrary(['spotify:track:id1', 'spotify:track:id2'])->get();

// Remove one or more items from the current user's library. Requires scope: user-library-modify.
Spotify::withToken($token)->removeFromLibrary(['spotify:track:id1', 'spotify:track:id2'])->get();
```

### Playlists

```php
// Get a playlist by ID.
Spotify::playlist('playlist_id')->get();

// Get a playlist's items by ID.
Spotify::playlistItems('playlist_id')->get();

// Get a playlist's cover image by ID.
Spotify::playlistCoverImage('playlist_id')->get();
```

### Playlist Item Management

Playlist write endpoints require a user access token with scope `playlist-modify-public` or `playlist-modify-private`.

```php
// Add one or more items to a playlist (optionally at a specific position).
Spotify::withToken($token)->addPlaylistItems('playlist_id', ['spotify:track:id1', 'spotify:track:id2'])->get();
Spotify::withToken($token)->addPlaylistItems('playlist_id', ['spotify:track:id1'], position: 0)->get();

// Reorder or replace items in a playlist.
Spotify::withToken($token)->updatePlaylistItems('playlist_id', ['spotify:track:id1'])->get();
Spotify::withToken($token)->updatePlaylistItems('playlist_id', ['spotify:track:id1'], rangeStart: 0, rangeLength: 1, insertBefore: 2)->get();

// Remove one or more items from a playlist.
Spotify::withToken($token)->removePlaylistItems('playlist_id', ['spotify:track:id1', 'spotify:track:id2'])->get();
```

### Search

> **Note:** The `limit` parameter has a maximum of **10** and a default of **5** for all search endpoints.

```php
// Search items by query. Provide a string or array to the second parameter.
Spotify::searchItems('query', 'album, artist, playlist, track')->get();

// Search albums by query.
Spotify::searchAlbums('query')->get();

// Search artists by query.
Spotify::searchArtists('query')->get();

// Search episodes by query.
Spotify::searchEpisodes('query')->get();

// Search playlists by query.
Spotify::searchPlaylists('query')->get();

// Search shows by query.
Spotify::searchShows('query')->get();

// Search tracks by query.
Spotify::searchTracks('query')->get();
```

### Shows

```php
// Get a show by ID.
Spotify::show('show_id')->get();

// Get the episodes of a show by ID.
Spotify::showEpisodes('show_id')->get();
```

### Tracks

```php
// Get a track by ID.
Spotify::track('track_id')->get();
```

## Tests
Run the tests like this:

```bash
vendor/bin/phpunit
```

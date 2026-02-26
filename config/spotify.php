<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    |
    | Here you may define the base URL of the Spotify API.
    |
    */

    'api_url' => env('SPOTIFY_API_URL', 'https://api.spotify.com/v1'),

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    |
    | The Client ID and Client Secret of your Spotify App.
    |
    */

    'auth' => [
        'client_id' => env('SPOTIFY_CLIENT_ID'),
        'client_secret' => env('SPOTIFY_CLIENT_SECRET'),
        'redirect_uri' => env('SPOTIFY_REDIRECT_URI'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Repository
    |--------------------------------------------------------------------------
    |
    | The token repository is used to store OAuth tokens for the Authorization
    | Code Flow. The default DatabaseTokenRepository persists tokens across
    | server restarts. Switch to CacheTokenRepository if you prefer not to
    | run a migration (note: cache-based tokens are lost on restart).
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

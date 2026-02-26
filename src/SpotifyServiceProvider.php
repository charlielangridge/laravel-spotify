<?php

namespace Aerni\Spotify;

use Aerni\Spotify\Clients\SpotifyClient;
use Aerni\Spotify\Contracts\TokenRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class SpotifyServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Spotify::class, function () {
            $defaultConfig = [
                'country' => config('spotify.default_config.country'),
                'locale' => config('spotify.default_config.locale'),
                'market' => config('spotify.default_config.market'),
            ];

            return new Spotify($defaultConfig);
        });

        $this->app->singleton(SpotifyAuth::class, function () {
            $clientId = config('spotify.auth.client_id');
            $clientSecret = config('spotify.auth.client_secret');

            return new SpotifyAuth($clientId, $clientSecret);
        });

        $this->app->bind(SpotifyClient::class, function () {
            return new SpotifyClient;
        });

        $this->app->bind(SpotifyRequest::class, function () {
            $accessToken = $this->app->make(SpotifyAuth::class)->getAccessToken();

            return new SpotifyRequest($accessToken);
        });

        $this->app->bind(TokenRepositoryInterface::class, config('spotify.token_repository'));

        $this->app->singleton(SpotifyAuthorizationCode::class, function ($app) {
            return new SpotifyAuthorizationCode(
                config('spotify.auth.client_id'),
                config('spotify.auth.client_secret'),
                config('spotify.auth.redirect_uri', ''),
                $app->make(TokenRepositoryInterface::class),
            );
        });
    }

    public function boot()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/spotify.php', 'spotify');

        $this->publishes([
            __DIR__.'/../config/spotify.php' => config_path('spotify.php'),
        ]);

        $this->publishes([
            __DIR__.'/../database/migrations/create_spotify_tokens_table.php'
                => database_path('migrations/'.date('Y_m_d_His').'_create_spotify_tokens_table.php'),
        ], 'migrations');
    }
}

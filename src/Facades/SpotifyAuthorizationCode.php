<?php

namespace Aerni\Spotify\Facades;

use Illuminate\Support\Facades\Facade;

class SpotifyAuthorizationCode extends Facade
{
    public static function getFacadeAccessor()
    {
        return \Aerni\Spotify\SpotifyAuthorizationCode::class;
    }
}

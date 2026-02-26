<?php

namespace Aerni\Spotify\Tests\Unit;

use Aerni\Spotify\Facades\Spotify;
use Aerni\Spotify\Tests\TestCase;

class BrowseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockSpotifyApi([
            '/browse/categories/dinner' => [
                'href' => 'https://api.spotify.com/v1/browse/categories/dinner',
                'id' => 'dinner',
                'name' => 'Dinner',
                'icons' => [[
                    'url' => 'https://i.scdn.co/image/category-dinner',
                    'height' => 275,
                    'width' => 275,
                ]],
            ],
            '/browse/categories/' => [
                'categories' => [
                    'href' => 'https://api.spotify.com/v1/browse/categories',
                    'items' => [[
                        'id' => 'dinner',
                        'name' => 'Dinner',
                        'icons' => [['url' => 'https://i.scdn.co/image/category-dinner', 'height' => 275, 'width' => 275]],
                    ]],
                    'limit' => 20,
                    'offset' => 0,
                    'total' => 1,
                ],
            ],
            '/browse/new-releases/' => [
                'albums' => [
                    'href' => 'https://api.spotify.com/v1/browse/new-releases',
                    'items' => [[
                        'id' => 'album-1',
                        'name' => 'New Release',
                        'type' => 'album',
                    ]],
                    'limit' => 20,
                    'offset' => 0,
                    'total' => 1,
                ],
            ],
        ]);
    }

    public function test_can_get_a_category(): void
    {
        $categoryId = 'dinner';

        $category = Spotify::category($categoryId)->get();

        $this->assertEquals($category['id'], $categoryId);
    }

    public function test_can_get_categories(): void
    {
        $categories = Spotify::categories()->get();

        $this->assertArrayHasKey('categories', $categories);
    }

    public function test_can_get_new_releases(): void
    {
        $newReleases = Spotify::newReleases()->get();

        $this->assertArrayHasKey('albums', $newReleases);
    }
}

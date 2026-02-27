<?php

namespace Aerni\Spotify\Tests\Unit;

use Aerni\Spotify\Facades\Spotify;
use Aerni\Spotify\Tests\TestCase;

class LibraryTest extends TestCase
{
    public function test_library_contains_throws_removed_endpoint_exception(): void
    {
        $this->expectException(\Aerni\Spotify\Exceptions\ValidatorException::class);
        $this->expectExceptionMessage('The [libraryContains] endpoint is no longer available');
        Spotify::withToken('mock-token')->libraryContains(['spotify:track:abc123'])->get();
    }

    public function test_save_to_library_throws_removed_endpoint_exception(): void
    {
        $this->expectException(\Aerni\Spotify\Exceptions\ValidatorException::class);
        $this->expectExceptionMessage('The [saveToLibrary] endpoint is no longer available');
        Spotify::withToken('mock-token')->saveToLibrary(['spotify:track:abc123'])->get();
    }

    public function test_save_to_library_with_comma_separated_string_throws_removed_endpoint_exception(): void
    {
        $this->expectException(\Aerni\Spotify\Exceptions\ValidatorException::class);
        $this->expectExceptionMessage('The [saveToLibrary] endpoint is no longer available');
        Spotify::withToken('mock-token')->saveToLibrary('spotify:track:abc123, spotify:track:def456')->get();
    }

    public function test_remove_from_library_throws_removed_endpoint_exception(): void
    {
        $this->expectException(\Aerni\Spotify\Exceptions\ValidatorException::class);
        $this->expectExceptionMessage('The [removeFromLibrary] endpoint is no longer available');
        Spotify::withToken('mock-token')->removeFromLibrary(['spotify:track:abc123'])->get();
    }
}

<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ProcessPosterImage;
use Database\Factories\ConcertFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProcessPosterImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_resizes_image_to_600px_wide()
    {
        // Arrange
        Storage::fake('public');
        Storage::disk('public')->put(
            'posters/example-poster.png',
            file_get_contents(base_path('tests/__fixtures__/full-size-poster.png'))
        );
        $concert = ConcertFactory::createUnpublished([
            'poster_image_path' => 'posters/example-poster.png'
        ]);

        // Act
        ProcessPosterImage::dispatch($concert);

        // Assert
        $resizedImage = Storage::disk('public')->get('posters/example-poster.png');
        list($width, $height) = getimagesizefromstring($resizedImage);
        $this->assertEquals(600, $width);
        $this->assertEquals(776, $height);
    }

    public function test_it_optimizes_the_image()
    {
        // Arrange
        Storage::fake('public');
        Storage::disk('public')->put(
            'posters/example-poster.png',
            file_get_contents(base_path('tests/__fixtures__/small-unoptimized-poster.png'))
        );
        $concert = ConcertFactory::createUnpublished([
            'poster_image_path' => 'posters/example-poster.png'
        ]);

        // Act
        ProcessPosterImage::dispatch($concert);

        // Assert
        $originalFileSize = filesize(base_path('tests/__fixtures__/small-unoptimized-poster.png'));
        $optimizedFileSize = Storage::disk('public')->size('posters/example-poster.png');
        $this->assertTrue($optimizedFileSize < $originalFileSize);
    }
}

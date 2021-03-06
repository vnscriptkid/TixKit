<?php

namespace Tests\Unit\Listeners;

use App\Events\ConcertAdded;
use App\Jobs\ProcessPosterImage;
use Database\Factories\ConcertFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\TestCase;
use Tests\TestCase as TestsTestCase;

class SchedulePosterImageProcessingTest extends TestsTestCase
{
    use RefreshDatabase;

    public function test_it_listens_for_added_event_and_queue_job_processing_image_if_poster_included()
    {
        // Arrange
        Queue::fake();
        $concert = ConcertFactory::createUnpublished([
            'poster_image_path' => 'posters/example-poster.png'
        ]);

        // Act
        ConcertAdded::dispatch($concert);

        // Assert
        Queue::assertPushed(ProcessPosterImage::class, function ($job) use ($concert) {
            return $concert->is($job->concert);
        });
    }

    public function test_it_does_not_queue_job_processing_image_if_no_poster_included()
    {
        // Arrange
        Queue::fake();
        $concert = ConcertFactory::createUnpublished(['poster_image_path' => null]);

        // Act
        ConcertAdded::dispatch($concert);

        // Assert
        Queue::assertNotPushed(ProcessPosterImage::class);
    }
}

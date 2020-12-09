<?php

namespace Tests\Unit;

use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConcertModelTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_get_ticket_price_in_dollars()
    {
        $concert = Concert::factory()->make([
            'ticket_price' => 3450
        ]);

        $this->assertEquals($concert->ticket_price_in_dollars, 34.50);
    }

    public function test_get_formatted_date() 
    {
        $concert = Concert::factory()->make([
            'date' => Carbon::parse('December 20, 2020 8:00pm')
        ]);

        $this->assertEquals($concert->formatted_date, 'December 20, 2020');
    }

    public function test_get_formatted_start_time() 
    {
        $concert = Concert::factory()->make([
            'date' => Carbon::parse('December 20, 2020 8:00pm')
        ]);

        $this->assertEquals($concert->formatted_start_time, '8:00pm');
    }

    public function test_published_custom_query_that_retrieves_only_published_concerts() 
    {
        $publishedConcert1 = Concert::factory()->published()->create();
        $publishedConcert2 = Concert::factory()->published()->create();
        $unpublishedConcert = Concert::factory()->unpublished()->create();

        $concerts = Concert::published()->get();

        $this->assertTrue($concerts->contains($publishedConcert1));
        $this->assertTrue($concerts->contains($publishedConcert2));
        $this->assertFalse($concerts->contains($unpublishedConcert));
    }
}

<?php

namespace Tests\Unit;

use App\Models\Concert;
use Carbon\Carbon;
use Tests\TestCase;

class ConcertModelTest extends TestCase
{
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

    public function test_get_formatted_date() {
        $concert = Concert::factory()->make([
            'date' => Carbon::parse('December 20, 2020 8:00pm')
        ]);

        $this->assertEquals($concert->formatted_date, 'December 20, 2020');
    }

    public function test_get_formatted_start_time() {
        $concert = Concert::factory()->make([
            'date' => Carbon::parse('December 20, 2020 8:00pm')
        ]);

        $this->assertEquals($concert->formatted_start_time, '8:00pm');
    }
}

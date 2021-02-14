<?php

namespace Tests\Unit;

use App\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConcertModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_ticket_price_in_dollars()
    {
        $concert = Concert::factory()->make(['ticket_price' => 3450]);

        $this->assertEquals($concert->ticket_price_in_dollars, 34.50);
    }

    public function test_get_formatted_date()
    {
        $concert = Concert::factory()->make(['date' => Carbon::parse('December 20, 2020 8:00pm')]);

        $this->assertEquals($concert->formatted_date, 'December 20, 2020');
    }

    public function test_get_formatted_start_time()
    {
        $concert = Concert::factory()->make(['date' => Carbon::parse('December 20, 2020 8:00pm')]);

        $this->assertEquals($concert->formatted_start_time, '8:00pm');
    }

    public function test_published_custom_query_that_retrieves_only_published_concerts()
    {
        // Arrange
        $publishedConcert1 = Concert::factory()->published()->create();
        $publishedConcert2 = Concert::factory()->published()->create();
        $unpublishedConcert = Concert::factory()->unpublished()->create();

        // Act
        $concerts = Concert::published()->get();

        // Assert
        $this->assertTrue($concerts->contains($publishedConcert1));
        $this->assertTrue($concerts->contains($publishedConcert2));
        $this->assertFalse($concerts->contains($unpublishedConcert));
    }

    public function test_add_tickets_and_tickets_remaining()
    {
        // Arrange
        $concert = Concert::factory()->published()->create();
        $this->assertEquals($concert->ticketsRemaining(), 0);

        // Act
        $concert->addTickets(5);

        // Assert
        $this->assertEquals($concert->ticketsRemaining(), 5);
    }

    public function test_tickets_remaining_does_not_include_purchased_ones()
    {
        // Arrange
        $concert = Concert::factory()->published()->create();
        $concert->addTickets(5);

        // Act
        $concert->orderTickets('join@gmail.com', 2);

        // Assert
        $this->assertEquals($concert->ticketsRemaining(), 3);
    }

    function test_trying_to_purchase_more_tickets_than_remain_throws_an_exception()
    {
        // Arrange
        $concert = Concert::factory()->published()->create()->addTickets(5);

        try {
            // Act
            $concert->orderTickets('john@gmail.com', 6);
        } catch (NotEnoughTicketsException $e) {
            // Assert
            $this->assertEquals($concert->ticketsRemaining(), 5);
            $this->assertFalse($concert->hasOrderFrom('john@gmail.com'));
            return;
        }

        $this->fail("Order succeeded even though there were not enough tickets remaining.");
    }

    function test_can_not_order_tickets_that_have_already_been_purchased()
    {
        // Arrange
        $concert = Concert::factory()->published()->create();
        $concert->addTickets(5);
        $concert->orderTickets('join@gmail.com', 3);

        try {
            // Act
            $concert->orderTickets('jane@gmail.com', 3);
        } catch (NotEnoughTicketsException $e) {
            // Assert
            $this->assertEquals($concert->ticketsRemaining(), 2);
            $this->assertFalse($concert->hasOrderFrom('jane@gmail.com'));
            return;
        }

        $this->fail("Order succeeded even though there were not enough tickets remaining.");
    }
}

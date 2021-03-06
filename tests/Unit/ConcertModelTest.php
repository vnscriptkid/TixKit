<?php

namespace Tests\Unit;

use App\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use Carbon\Carbon;
use Database\Factories\ConcertFactory;
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
        $publishedConcert1 = ConcertFactory::createPublished();
        $publishedConcert2 = ConcertFactory::createPublished();
        $unpublishedConcert = ConcertFactory::createUnpublished();

        // Act
        $concerts = Concert::published()->get();

        // Assert
        $this->assertTrue($concerts->contains($publishedConcert1));
        $this->assertTrue($concerts->contains($publishedConcert2));
        $this->assertFalse($concerts->contains($unpublishedConcert));
    }

    public function test_tickets_remaining()
    {
        // Arrange
        $concert = Concert::factory()->create(['ticket_quantity' => 5]);
        $this->assertEquals($concert->ticketsRemaining(), 0);

        // Act
        $concert->publish();

        // Assert
        $this->assertEquals($concert->ticketsRemaining(), 5);
    }

    public function test_tickets_remaining_does_not_include_purchased_ones()
    {
        // Arrange
        $concert = ConcertFactory::createPublished(['ticket_quantity' => 5]);
        $order = Order::factory()->create();
        $order->tickets()->saveMany($concert->tickets->take(2));

        // Act + Assert
        $this->assertEquals($concert->ticketsRemaining(), 3);
    }

    public function test_tickets_sold_include_only_tickets_associated_with_an_order()
    {
        // Arrange
        $concert = ConcertFactory::createPublished(['ticket_quantity' => 5]);
        $order = Order::factory()->create();
        $order->tickets()->saveMany($concert->tickets->take(2));

        // Act + Assert
        $this->assertEquals($concert->ticketsSold(), 2);
    }

    function test_trying_to_purchase_more_tickets_than_remain_throws_an_exception()
    {
        // Arrange
        $concert = Concert::factory()->create(['ticket_quantity' => 5]);
        $concert->publish();

        try {
            // Act
            $concert->reserveTickets('john@gmail.com', 6);
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
        $concert = ConcertFactory::createPublished(['ticket_quantity' => 10]);
        $order = Order::factory()->create();
        $order->tickets()->saveMany($concert->tickets->take(6));

        try {
            // Act
            $concert->reserveTickets('jane@gmail.com', 5);
        } catch (NotEnoughTicketsException $e) {
            // Assert
            $this->assertEquals($concert->ticketsRemaining(), 4);
            $this->assertFalse($concert->hasOrderFrom('jane@gmail.com'));
            return;
        }

        $this->fail("Order succeeded even though there were not enough tickets remaining.");
    }

    public function test_reserving_tickets_for_an_email_before_charging()
    {
        $concert = ConcertFactory::createPublished(['ticket_quantity' => 5]);

        $reservation = $concert->reserveTickets('john@gmail.com', 2);

        $this->assertEquals($concert->ticketsRemaining(), 3);
        $this->assertCount(2, $reservation->tickets());
        $this->assertEquals('john@gmail.com', $reservation->email());
    }

    public function test_can_not_reserve_tickets_that_has_already_been_purchased()
    {
        $concert = ConcertFactory::createPublished(['ticket_quantity' => 4]);
        $concert->reserveTickets('john@gmail.com', 3);

        try {
            $concert->reserveTickets('jane@gmail.com', 2);
        } catch (NotEnoughTicketsException $e) {
            $this->assertEquals($concert->ticketsRemaining(), 1);
            return;
        }

        $this->fail("Reservation succeeded even though tickets were already sold.");
    }

    public function test_can_not_reserve_tickets_that_has_already_been_reserved()
    {
        $concert = ConcertFactory::createPublished(['ticket_quantity' => 4]);

        $concert->reserveTickets('john@gmail.com', 3);

        try {
            $concert->reserveTickets('jane@gmail.com', 2);
        } catch (NotEnoughTicketsException $e) {
            $this->assertEquals($concert->ticketsRemaining(), 1);
            return;
        }

        $this->fail("Reservation succeeded even though tickets were already reserved.");
    }

    public function test_can_be_published()
    {
        $concert = Concert::factory()->create([
            'published_at' => null,
            'ticket_quantity' => 200
        ]);
        $this->assertFalse($concert->isPublished());
        $this->assertEquals($concert->ticketsRemaining(), 0);

        $concert->publish();

        $this->assertEquals($concert->ticketsRemaining(), 200);
        $this->assertTrue($concert->isPublished());
    }

    public function test_tickets_total_includes_all_kinds_of_tickets()
    {
        $concert = ConcertFactory::createPublished(['ticket_quantity' => 5]);
        $order = Order::factory()->create(['concert_id' => $concert->id]);

        $order->tickets()->saveMany($concert->tickets->take(2));

        $this->assertEquals(5, $concert->ticketsTotal());
    }

    public function test_percent_sold_out()
    {
        $concert = ConcertFactory::createPublished(['ticket_quantity' => 7]);
        $order = Order::factory()->create(['concert_id' => $concert->id]);

        $order->tickets()->saveMany($concert->tickets->take(2));

        $this->assertEquals(28.57, $concert->percentSoldOut());
    }

    public function test_revenue_in_dollars()
    {
        $concert = Concert::factory()->create();
        $orderA = Order::factory()->create(['concert_id' => $concert->id, 'amount' => 1234]);
        $orderB = Order::factory()->create(['concert_id' => $concert->id, 'amount' => 2380]);
        $concert->tickets()->saveMany(Ticket::factory(2)->create(['order_id' => $orderA]));
        $concert->tickets()->saveMany(Ticket::factory(5)->create(['order_id' => $orderB]));

        $this->assertEquals(36.14, $concert->revenueInDollars());
    }

    public function test_has_poster()
    {
        $concert = Concert::factory()->create();
        $this->assertFalse($concert->hasPoster());
        $concert->update(['poster_image_path' => 'posters/example-poster.png']);
        $this->assertTrue($concert->hasPoster());
    }
}

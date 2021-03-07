<?php

namespace Tests\Unit;

use App\Billing\FakePaymentGateway;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\LegacyMockInterface;
use Tests\TestCase;

class ReservationModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculating_total_price()
    {
        // Arrange
        $tickets = collect([
            (object) ['price' => 1000],
            (object) ['price' => 1000],
        ]);
        // Act
        $reservation = new Reservation('john@gmail.com', $tickets);

        // Assert
        $this->assertEquals($reservation->totalPrice(), 2000);
    }

    public function test_reservation_can_be_cancelled()
    {
        // Arrange
        $tickets = collect([
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
        ]);
        // Act
        $reservation = new Reservation('john@gmail.com', $tickets);
        $reservation->cancel();

        // Assert
        $tickets->each(function (LegacyMockInterface $ticket) {
            $ticket->shouldHaveReceived('release');
        });
    }

    public function test_get_tickets()
    {
        // Arrange
        $tickets = collect([
            (object) ['price' => 1000],
            (object) ['price' => 1000],
        ]);
        // Act
        $reservation = new Reservation('john@gmail.com', $tickets);

        // Assert
        $this->assertEquals($reservation->tickets(), $tickets);
    }

    public function test_get_email()
    {
        // Arrange
        $tickets = collect([
            (object) ['price' => 1000],
            (object) ['price' => 1000],
        ]);
        // Act
        $reservation = new Reservation('john@gmail.com', $tickets);

        // Assert
        $this->assertEquals($reservation->email(), 'john@gmail.com');
    }

    public function test_completing_a_reservation_to_get_an_order()
    {
        $tickets = Ticket::factory(3)->create(['order_id' => null]);
        $reservation = new Reservation('john@gmail.com', $tickets);
        $paymentGateway = new FakePaymentGateway();

        $order = $reservation->complete($paymentGateway, $paymentGateway->getValidTestToken(), 'test-acc-id-123');

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($order->ticketQuantity(), 3);
        $this->assertEquals($order->email, 'john@gmail.com');
        $this->assertEquals($order->amount, $reservation->totalPrice());
    }
}

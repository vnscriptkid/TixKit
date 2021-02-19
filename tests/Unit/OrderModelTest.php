<?php

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Order;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_is_converted_to_array()
    {
        $concert = Concert::factory()->published()->create(['ticket_price' => 1200])->addTickets(10);
        $order = $concert->orderTickets('john@gmail.com', 5);

        $this->assertEquals($order->toArray(), [
            'email' => 'john@gmail.com',
            'ticket_quantity' => 5,
            'amount' => 6000
        ]);
    }

    public function test_creating_order_from_tickets_and_email_and_amount()
    {
        // Arrange
        $concert = Concert::factory()->published()->create(['ticket_price' => 1200])->addTickets(10);
        $tickets = $concert->findTickets(5);

        // Act
        $order = Order::forTickets('john@gmail.com', $tickets, 6000);

        // Assert
        $this->assertEquals($order->email, 'john@gmail.com');
        $this->assertEquals($order->amount, 6000);
        $this->assertEquals($concert->ticketsRemaining(), 5);
    }

    public function test_creating_order_from_reservation()
    {
        // Arrange
        $concert = Concert::factory()->published()->create(['ticket_price' => 1200])->addTickets(10);
        $reservation = $concert->reserveTickets('john@gmail.com', 5);

        // Act
        $order = Order::forReservation($reservation);

        // Assert
        $this->assertEquals($order->email, 'john@gmail.com');
        $this->assertEquals($order->amount, 6000);
        $this->assertEquals($concert->ticketsRemaining(), 5);
    }

    public function test_find_by_confirmation_number_successfully()
    {
        // Arrange
        $order = Order::factory()->create(['confirmation_number' => 'CONFIRMATIONNUMBERXYZ']);

        // Act
        $foundOrder = Order::findByConfirmationNumber('CONFIRMATIONNUMBERXYZ');

        // Assert
        $this->assertEquals($order->id, $foundOrder->id);
    }

    public function test_find_by_non_existed_confirmation_number_throws_exception()
    {
        // Arrange
        Order::factory()->create();

        // Act
        try {
            Order::findByConfirmationNumber('NON_EXISTED_NUMBER');
        } catch (ModelNotFoundException $e) {
            // Assert
            $this->assertNotNull($e);
            return;
        }

        $this->fail();
    }
}

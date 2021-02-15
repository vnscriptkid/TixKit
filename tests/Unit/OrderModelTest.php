<?php

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Order;
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

    public function test_order_can_be_canceled()
    {
        // Arrange
        $concert = Concert::factory()->published()->create()->addTickets(10);
        $order = $concert->orderTickets('john@gmail.com', 5);
        $this->assertEquals($concert->ticketsRemaining(), 5);

        // Act
        $order->cancel();

        // Assert
        $this->assertEquals($concert->ticketsRemaining(), 10);
        $this->assertNull(Order::find($order->id));
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
}

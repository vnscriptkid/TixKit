<?php

namespace Tests\Unit;

use App\Billing\Charge;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_is_converted_to_array()
    {
        $order = Order::factory()->create([
            'email' => 'john@gmail.com',
            'amount' => 6000,
            'confirmation_number' => 'CONFIRMED123'
        ]);

        $order->tickets()->saveMany(Ticket::factory(10)->make());

        $this->assertEquals($order->toArray(), [
            'email' => 'john@gmail.com',
            'ticket_quantity' => 10,
            'amount' => 6000,
            'confirmation_number' => 'CONFIRMED123'
        ]);
    }

    public function test_creating_order_from_tickets_and_email_and_charge_object()
    {
        // Arrange
        $tickets = Ticket::factory(5)->create(['order_id' => null]);
        $charge = new Charge([
            'amount' => 3000,
            'card_last_four' => '4242'
        ]);

        // Act
        $order = Order::forTickets('john@gmail.com', $tickets, $charge);

        // Assert
        $this->assertEquals($order->email, 'john@gmail.com');
        $this->assertEquals($order->amount, 3000);
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

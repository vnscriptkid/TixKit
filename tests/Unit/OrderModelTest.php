<?php

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_can_be_canceled()
    {
        // create a concert
        $concert = Concert::factory()->published()->create();
        $concert->addTickets(10);

        // create an order
        $order = $concert->orderTickets('john@gmail.com', 5);
        $this->assertEquals($concert->ticketsRemaining(), 5);

        // cancel that order
        $order->cancel();

        // assert: order not longer exists, tickets are deleted
        $this->assertEquals($concert->ticketsRemaining(), 10);
        $this->assertNull(Order::find($order->id));
    }
}

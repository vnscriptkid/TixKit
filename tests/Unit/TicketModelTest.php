<?php

namespace Tests\Unit;

use App\Models\Concert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_can_be_released()
    {
        // Arrange
        $concert = Concert::factory()->published()->create()->addTickets(1);
        $order = $concert->orderTickets('john@gmail.com', 1);
        $ticket = $order->tickets()->first();
        $this->assertNotNull($ticket->order_id);

        // Act
        $ticket->release();

        // Assert
        $this->assertNull($ticket->order_id);
    }
}

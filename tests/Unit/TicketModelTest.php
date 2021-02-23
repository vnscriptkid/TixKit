<?php

namespace Tests\Unit;

use App\Facades\TicketCode;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_can_be_reserved()
    {
        $ticket = Ticket::factory()->create();

        $this->assertNull($ticket->reserved_at);

        $ticket->reserve();

        $this->assertNotNull($ticket->reserved_at);
    }

    public function test_ticket_can_be_released()
    {
        $ticket = Ticket::factory()->reserved()->create();

        $this->assertNotNull($ticket->reserved_at);

        $ticket->release();

        $this->assertNull($ticket->reserved_at);
    }

    public function test_ticket_can_be_claimed_for_order()
    {
        // Arrange
        $ticket = Ticket::factory()->create();
        $order = Order::factory()->create();
        TicketCode::shouldReceive('generate')->andReturn('CODE123');
        $this->assertEquals($ticket->code, null);

        // Act
        $ticket->claimFor($order);

        // Assert
        $this->assertContains($ticket->id, $order->tickets->pluck('id'));
        $this->assertEquals($ticket->code, 'CODE123');
    }
}

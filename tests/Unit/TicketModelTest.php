<?php

namespace Tests\Unit;

use App\Models\Concert;
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
}

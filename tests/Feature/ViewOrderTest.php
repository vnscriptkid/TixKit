<?php

namespace Tests\Feature;

use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_order()
    {
        $this->withoutExceptionHandling();
        // Arrange
        $concert = Concert::factory()->create();
        $order = Order::factory()->create([
            'confirmation_number' => 'CONFIRMATIONNUMBER123',
            'amount' => 4550,
            'card_last_four' => '4242'
        ]);
        Ticket::factory()->create([
            'concert_id' => $concert->id,
            'order_id' => $order->id,
            'code' => 'TICKETCODE123'
        ]);
        Ticket::factory()->create([
            'concert_id' => $concert->id,
            'order_id' => $order->id,
            'code' => 'TICKETCODE456'
        ]);

        // Act
        $response = $this->get("/orders/{$order->confirmation_number}");

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('order', function ($viewOrder) use ($order) {
            return $viewOrder->id === $order->id;
        });
        $response->assertSee('$45.50');
        $response->assertSee('CONFIRMATIONNUMBER123');
        $response->assertSee('**** **** **** 4242');
        $response->assertSee('TICKETCODE123');
        $response->assertSee('TICKETCODE456');
    }
}

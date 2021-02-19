<?php

namespace Tests\Feature;

use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ViewOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_order()
    {
        $this->withoutExceptionHandling();
        // Arrange
        // Create concert
        $concert = Concert::factory()->create();
        // Create order
        $order = Order::factory()->create([
            'confirmation_number' => 'CONFIRMATIONNUMBER123'
        ]);
        // Create tickets
        Ticket::factory()->create([
            'concert_id' => $concert->id,
            'order_id' => $order->id
        ]);

        // Act
        // View order
        $response = $this->get("/orders/{$order->confirmation_number}");

        // Assert
        $response->assertStatus(200);
        // Correct info
        $response->assertViewHas('order', function ($viewOrder) use ($order) {
            return $viewOrder->id === $order->id;
        });
    }
}

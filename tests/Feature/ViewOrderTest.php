<?php

namespace Tests\Feature;

use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_order()
    {
        $this->withoutExceptionHandling();
        // Arrange
        $concert = Concert::factory()->create([
            'title' => 'The Red Chord',
            'subtitle' => 'with Animosity and Lethargy',
            'date' => Carbon::parse('March 12, 2017 8:00pm'),
            'ticket_price' => 4250,
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Example Lane',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '17916',
        ]);
        $order = Order::factory()->create([
            'confirmation_number' => 'CONFIRMATIONNUMBER123',
            'amount' => 4550,
            'card_last_four' => '4242',
            'email' => 'john@example.com',
        ]);
        $ticketA = Ticket::factory()->create([
            'concert_id' => $concert->id,
            'order_id' => $order->id,
            'code' => 'TICKETCODE123'
        ]);
        $ticketB = Ticket::factory()->create([
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
        $response->assertSee('The Red Chord');
        $response->assertSee('with Animosity and Lethargy');
        $response->assertSee('The Mosh Pit');
        $response->assertSee('123 Example Lane');
        $response->assertSee('Laraville, ON');
        $response->assertSee('17916');
        $response->assertSee('john@example.com');
        $response->assertSee('2017-03-12 20:00');
    }
}

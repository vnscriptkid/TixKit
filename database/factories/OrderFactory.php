<?php

namespace Database\Factories;

use App\Facades\OrderConfirmationNumber;
use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'email' => 'example@gmail.com',
            'confirmation_number' => OrderConfirmationNumber::generate(),
            'amount' => 1000,
            'card_last_four' => '4242424242424242',
            'concert_id' => function () {
                return Concert::factory()->create()->id;
            }
        ];
    }

    public static function createForConcert(Concert $concert, $overrides = [], $ticketQuantity = 1)
    {
        $order = Order::factory()->create($overrides);
        $tickets = Ticket::factory($ticketQuantity)->create([
            'concert_id' => $concert->id
        ]);
        $order->tickets()->saveMany($tickets);
        return $order;
    }
}

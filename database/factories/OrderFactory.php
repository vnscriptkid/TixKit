<?php

namespace Database\Factories;

use App\Models\Concert;
use App\Models\Order;
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
            'confirmation_number' => 'CONFIRMATIONNUMBER123',
            'amount' => 1000,
            'card_last_four' => '4242424242424242',
            'concert_id' => function () {
                return Concert::factory()->create()->id;
            }
        ];
    }
}

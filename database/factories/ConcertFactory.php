<?php

namespace Database\Factories;

use App\Models\Concert;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConcertFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Concert::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => function () {
                return User::factory()->create()->id;
            },
            'title' => 'Example Title',
            'subtitle' => 'with some fake subTitles',
            'date' => Carbon::parse('+2 weeks'),
            'ticket_price' => 1250,
            'venue' => 'The House',
            'venue_address' => 'Some mysterious street',
            'city' => 'Hanoi',
            'state' => 'North',
            'zip' => '20056',
            'ticket_quantity' => 50,
            'additional_information' => 'Feel free to contact us by email: example@gmail.com'
        ];
    }

    public static function createPublished($overrides = [])
    {
        $concert = Concert::factory()->create($overrides);
        $concert->publish();
        return $concert;
    }

    public static function createUnpublished($overrides = [])
    {
        return Concert::factory()->unpublished()->create($overrides);
    }

    public function unpublished()
    {
        return $this->state(function () {
            return [
                'published_at' => null
            ];
        });
    }
}

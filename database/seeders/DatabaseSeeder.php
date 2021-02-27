<?php

namespace Database\Seeders;

use App\Models\Concert;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\ConcertFactory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $user = User::factory()->create([
            'email' => 'vnscriptkid@gmail.com',
            'password' => bcrypt('123456')
        ]);

        ConcertFactory::createPublished([
            'title' => 'Example Title',
            'subtitle' => 'with some fake subTitles',
            'date' => Carbon::parse('+2 weeks'),
            'ticket_price' => 1250,
            'venue' => 'The House',
            'venue_address' => 'Some mysterious street',
            'city' => 'Hanoi',
            'state' => 'North',
            'zip' => '20056',
            'additional_information' => 'Feel free to contact us by email: example@gmail.com',
            'ticket_quantity' => 5,
            'user_id' => $user->id
        ]);

        ConcertFactory::createUnpublished([
            'title' => 'The Olympic Games',
            'subtitle' => 'So much fun',
            'date' => Carbon::parse('+2 weeks'),
            'ticket_price' => 3450,
            'venue' => 'The Stadium',
            'venue_address' => 'Lieu Giai Street',
            'city' => 'HCM',
            'state' => 'South',
            'zip' => '30044',
            'additional_information' => 'Feel free to contact us by email: example@gmail.com',
            'ticket_quantity' => 200,
            'user_id' => $user->id
        ]);
    }
}

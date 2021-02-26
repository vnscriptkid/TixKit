<?php

namespace Database\Seeders;

use App\Models\Concert;
use App\Models\User;
use Carbon\Carbon;
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
        // \App\Models\User::factory(10)->create();
        $concert = Concert::factory()->create([
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
            'ticket_quantity' => 5
        ]);
        $concert->publish();

        User::factory()->create([
            'email' => 'vnscriptkid@gmail.com',
            'password' => bcrypt('123456')
        ]);
    }
}

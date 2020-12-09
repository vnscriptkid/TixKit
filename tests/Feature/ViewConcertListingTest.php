<?php

namespace Tests\Feature;

use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewConcertListingTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_user_can_view_a_concert_listing()
    {
        $this->withoutExceptionHandling();
        // Arrange
        $concert = Concert::create([
            'title' => 'Vietnamese traditional folks dance',
            'subtitle' => 'with some experts on the field',
            'date' => Carbon::parse('December 20, 2020 8:00pm'),
            'ticket_price' => 1250,
            'venue' => 'Hanoi Opera House',
            'venue_address' => '20A Trang Tien Street, Hoan Kiem District',
            'city' => 'Hanoi',
            'state' => 'North',
            'zip' => '20056',
            'additional_information' => 'Feel free to contact us by email: folkdance@gmail.com'
        ]);

        // Actions
        $response = $this->get('/concerts/' . $concert->id);
        
        // Assert
        $response->assertStatus(200);
        $response->assertSeeText($concert['title']);
        $response->assertSeeText($concert['subtitle']);
        $response->assertSeeText('December 20, 2020');
        $response->assertSeeText('8:00pm');
        $response->assertSeeText('12.50');
        $response->assertSeeText($concert['venue']);
        $response->assertSeeText($concert['venue_address']);
        $response->assertSeeText($concert['city']);
        $response->assertSeeText($concert['state']);
        $response->assertSeeText($concert['zip']);
        $response->assertSeeText($concert['additional_information']);
    }
}

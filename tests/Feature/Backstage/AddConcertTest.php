<?php

namespace Tests\Feature\Backstage;

use App\Models\Concert;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AddConcertTest extends TestCase
{
    use RefreshDatabase;

    private function validParams($overrides = [])
    {
        return array_merge([
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date' => '2017-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => 75,
        ], $overrides);
    }

    public function test_logged_in_user_can_view_add_concert_form()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/backstage/concerts/new');

        $response->assertStatus(200);
    }

    public function test_guests_can_not_view_add_concert_form()
    {
        $response = $this->get('/backstage/concerts/new');

        $response->assertRedirect('/login');
    }

    public function test_adding_valid_concert()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date' => '2017-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => 75,
        ]);

        tap(Concert::first(), function ($concert) use ($response, $user) {
            $response->assertRedirect('backstage/concerts');

            $this->assertEquals($user->id, $concert->user->id);

            $this->assertFalse($concert->isPublished());
            $this->assertEquals('No Warning', $concert->title);
            $this->assertEquals('with Cruel Hand and Backtrack', $concert->subtitle);
            $this->assertEquals("You must be 19 years of age to attend this concert.", $concert->additional_information);
            $this->assertEquals(Carbon::parse('2017-11-18 8:00pm'), $concert->date);
            $this->assertEquals('The Mosh Pit', $concert->venue);
            $this->assertEquals('123 Fake St.', $concert->venue_address);
            $this->assertEquals('Laraville', $concert->city);
            $this->assertEquals('ON', $concert->state);
            $this->assertEquals('12345', $concert->zip);
            $this->assertEquals(3250, $concert->ticket_price);
            $this->assertEquals(75, $concert->ticket_quantity);
            $this->assertEquals(0, $concert->ticketsRemaining());
        });
    }

    public function test_guests_can_not_add_concert()
    {
        $response = $this->post('/backstage/concerts', $this->validParams());

        $response->assertRedirect('/login');
        $this->assertEquals(Concert::count(), 0);
    }

    public function test_posting_new_concert_with_empty_title()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/backstage/concerts/add')
            ->post('/backstage/concerts', $this->validParams(['title' => '']));

        $response->assertRedirect('/backstage/concerts/add');
        $response->assertSessionHasErrors(['title']);
        $this->assertEquals(Concert::count(), 0);
    }

    public function test_subtitle_is_optional()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/add')
            ->post('/backstage/concerts', $this->validParams(['subtitle' => '']));

        tap(Concert::first(), function ($concert) use ($response) {
            $response->assertRedirect('/backstage/concerts');
            $this->assertNull($concert->subtitle);
        });
    }

    function test_additional_information_is_optional()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/add')
            ->post('/backstage/concerts', $this->validParams(['additional_information' => '']));

        tap(Concert::first(), function ($concert) use ($response) {
            $response->assertRedirect('/backstage/concerts');
            $this->assertNull($concert->additional_information);
        });
    }

    function test_date_is_required()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['date' => '']));

        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('date');
        $this->assertEquals(0, Concert::count());
    }

    function test_date_must_be_a_valid_date()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['date' => 'not a date']));

        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('date');
        $this->assertEquals(0, Concert::count());
    }

    function test_time_is_required()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['time' => '']));

        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('time');
        $this->assertEquals(0, Concert::count());
    }

    function test_time_must_be_a_valid_time()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['time' => 'not a time']));

        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('time');
        $this->assertEquals(0, Concert::count());
    }

    function test_venue_is_required()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['venue' => '']));

        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('venue');
        $this->assertEquals(0, Concert::count());
    }

    function test_venue_address_is_required()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['venue_address' => '']));

        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('venue_address');
        $this->assertEquals(0, Concert::count());
    }

    function test_city_is_required()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['city' => '']));

        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('city');
        $this->assertEquals(0, Concert::count());
    }

    function test_state_is_required()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['state' => '']));

        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('state');
        $this->assertEquals(0, Concert::count());
    }

    function test_zip_is_required()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['zip' => '']));

        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('zip');
        $this->assertEquals(0, Concert::count());
    }

    function test_ticket_price_is_required()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['ticket_price' => '']));

        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('ticket_price');
        $this->assertEquals(0, Concert::count());
    }

    function test_ticket_price_must_be_numeric()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['ticket_price' => 'not a price']));

        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('ticket_price');
        $this->assertEquals(0, Concert::count());
    }

    function test_ticket_price_must_be_at_least_5()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['ticket_price' => 4.99]));

        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('ticket_price');
        $this->assertEquals(0, Concert::count());
    }

    function test_ticket_quantity_is_required()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['ticket_quantity' => '']));

        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('ticket_quantity');
        $this->assertEquals(0, Concert::count());
    }

    function test_ticket_quantity_must_be_numeric()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['ticket_quantity' => 'not a number']));

        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('ticket_quantity');
        $this->assertEquals(0, Concert::count());
    }

    function test_ticket_quantity_must_be_at_least_1()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['ticket_quantity' => 0]));

        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('ticket_quantity');
        $this->assertEquals(0, Concert::count());
    }

    function test_poster_image_is_uploaded_if_included()
    {
        // Arrange
        Storage::fake('s3');
        $user = User::factory()->create();
        $file = File::image('concert-poster.png', 850, 1100);

        // Act
        $response = $this->actingAs($user)
            ->post('/backstage/concerts', $this->validParams(['poster_image' => $file]));

        // Assert
        tap(Concert::first(), function ($concert) use ($response, $file) {
            $response->assertRedirect('/backstage/concerts');
            $this->assertNotNull($concert->poster_image_path);
            Storage::disk('s3')->assertExists($concert->poster_image_path);
            $this->assertFileEquals(
                $file->getPathname(),
                Storage::disk('s3')->path($concert->poster_image_path)
            );
        });
    }

    function test_poster_image_must_be_an_image()
    {
        // Arrange
        Storage::fake('s3');

        $user = User::factory()->create();
        $file = File::create('not-an-image.pdf');

        // Act
        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams(['poster_image' => $file]));

        // Assert
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('poster_image');
        $this->assertEquals(0, Concert::count());
    }

    function test_poster_image_must_be_at_least_600px_wide()
    {
        // Arrange
        Storage::fake('s3');
        $user = User::factory()->create();
        $file = File::image('poster.png', 599, 775);

        // Act
        $response = $this->actingAs($user)
            ->from('/backstage/concerts/new')
            ->post('/backstage/concerts', $this->validParams([
                'poster_image' => $file,
            ]));

        // Assert
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('poster_image');
        $this->assertEquals(0, Concert::count());
    }

    function test_poster_image_must_have_letter_aspect_ratio()
    {
        // Arrange
        Storage::fake('s3');
        $user = User::factory()->create();
        $file = File::image('poster.png', 851, 1100);

        // Act
        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', $this->validParams([
            'poster_image' => $file,
        ]));

        // Assert
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('poster_image');
        $this->assertEquals(0, Concert::count());
    }

    function test_poster_image_is_optional()
    {
        $this->withoutExceptionHandling();
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->post('/backstage/concerts', $this->validParams([
            'poster_image' => null,
        ]));

        // Assert
        tap(Concert::first(), function ($concert) use ($response, $user) {
            $response->assertRedirect('/backstage/concerts');
            $this->assertTrue($concert->user->is($user));
            $this->assertNull($concert->poster_image_path);
        });
    }
}

<?php

namespace Tests\Feature\Backstage;

use App\Models\Concert;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\ConcertFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditConcertTest extends TestCase
{
    use RefreshDatabase;

    private function validParams($overrides = [])
    {
        return array_merge([
            'title' => 'New title',
            'subtitle' => 'New subtitle',
            'additional_information' => 'New additional information',
            'date' => '2018-12-12',
            'time' => '8:00pm',
            'venue' => 'New venue',
            'venue_address' => 'New address',
            'city' => 'New city',
            'state' => 'New state',
            'zip' => '99999',
            'ticket_price' => '72.50',
            'ticket_quantity' => 50
        ], $overrides);
    }

    private function assertRemainTheSame($oldConcertData, $concertObj)
    {
        $old = collect($oldConcertData)->map(function ($item) {
            return strval($item);
        });

        $new = collect($concertObj->fresh()->getAttributes());

        foreach ($old as $key => $value) {
            $this->assertTrue($new[$key] === $value);
        }
    }

    private function oldConcertData($user)
    {
        return [
            'user_id' => $user->id,
            'title' => 'Old title',
            'subtitle' => 'Old subtitle',
            'additional_information' => 'Old additional information',
            'date' => Carbon::parse('2017-01-01 5:00pm'),
            'venue' => 'Old venue',
            'venue_address' => 'Old address',
            'city' => 'Old city',
            'state' => 'Old state',
            'zip' => '00000',
            'ticket_price' => 2000,
            'ticket_quantity' => 10
        ];
    }

    public function test_promoters_can_view_the_edit_form_for_their_own_unpublished_concerts()
    {
        $user = User::factory()->create();
        $concert = Concert::factory()->unpublished()->create(['user_id' => $user->id]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(200);
        $this->assertTrue($response->viewData('concert')->is($concert));
    }

    public function test_promoters_cannot_view_the_edit_form_for_their_own_published_concerts()
    {
        $user = User::factory()->create();
        $concert = ConcertFactory::createPublished(['user_id' => $user->id]);

        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(403);
    }

    function test_promoters_cannot_view_the_edit_form_for_other_concerts()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $concertOfOther = ConcertFactory::createPublished(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concertOfOther->id}/edit");

        $response->assertStatus(404);
    }

    function test_promoters_see_a_404_when_attempting_to_view_the_edit_form_for_a_concert_that_does_not_exist()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get("/backstage/concerts/999/edit");

        $response->assertStatus(404);
    }

    function test_guests_are_asked_to_login_when_attempting_to_view_the_edit_form_for_any_concert()
    {
        $someone = User::factory()->create();
        $concertOfSomeone = Concert::factory()->create(['user_id' => $someone->id]);

        $response = $this->get("/backstage/concerts/{$concertOfSomeone->id}/edit");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    function test_guests_are_asked_to_login_when_attempting_to_view_the_edit_form_for_a_concert_that_does_not_exist()
    {
        $response = $this->get("/backstage/concerts/999/edit");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    function test_promoters_can_edit_their_own_unpublished_concerts()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = Concert::factory()->create([
            'user_id' => $user->id,
            'title' => 'Old title',
            'subtitle' => 'Old subtitle',
            'additional_information' => 'Old additional information',
            'date' => Carbon::parse('2017-01-01 5:00pm'),
            'venue' => 'Old venue',
            'venue_address' => 'Old address',
            'city' => 'Old city',
            'state' => 'Old state',
            'zip' => '00000',
            'ticket_price' => 2000,
            'ticket_quantity' => 10
        ]);
        $this->assertFalse($concert->isPublished());

        // Act
        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", [
            'title' => 'New title',
            'subtitle' => 'New subtitle',
            'additional_information' => 'New additional information',
            'date' => '2018-12-12',
            'time' => '8:00pm',
            'venue' => 'New venue',
            'venue_address' => 'New address',
            'city' => 'New city',
            'state' => 'New state',
            'zip' => '99999',
            'ticket_price' => '72.50',
            'ticket_quantity' => 50
        ]);

        // Assert
        $response->assertRedirect("/backstage/concerts");
        tap($concert->fresh(), function ($concert) {
            $this->assertEquals('New title', $concert->title);
            $this->assertEquals('New subtitle', $concert->subtitle);
            $this->assertEquals('New additional information', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2018-12-12 8:00pm'), $concert->date);
            $this->assertEquals('New venue', $concert->venue);
            $this->assertEquals('New address', $concert->venue_address);
            $this->assertEquals('New city', $concert->city);
            $this->assertEquals('New state', $concert->state);
            $this->assertEquals('99999', $concert->zip);
            $this->assertEquals(7250, $concert->ticket_price);
            $this->assertEquals(50, $concert->ticket_quantity);
        });
    }

    function test_promoters_can_not_edit_unpublished_concert_of_other()
    {
        // Arrange
        $me = User::factory()->create();
        $otherUser = User::factory()->create();

        $concertOfOther = Concert::factory()->create($this->oldConcertData($otherUser));
        $this->assertFalse($concertOfOther->isPublished());

        // Act
        $response = $this->actingAs($me)->patch("/backstage/concerts/{$concertOfOther->id}", [
            'title' => 'New title',
            'subtitle' => 'New subtitle',
            'additional_information' => 'New additional information',
            'date' => '2018-12-12',
            'time' => '8:00pm',
            'venue' => 'New venue',
            'venue_address' => 'New address',
            'city' => 'New city',
            'state' => 'New state',
            'zip' => '99999',
            'ticket_price' => '72.50',
            'ticket_quantity' => 50
        ]);

        // Assert
        $response->assertStatus(404);
        $this->assertRemainTheSame($this->oldConcertData($otherUser), $concertOfOther);
    }

    function test_promoters_cannot_edit_published_concerts()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = ConcertFactory::createPublished($this->oldConcertData($user));
        $this->assertTrue($concert->isPublished());

        // Act
        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", $this->validParams());

        // Assert
        $response->assertStatus(403);
        $this->assertRemainTheSame($this->oldConcertData($user), $concert);
    }

    function test_guests_cannot_edit_concerts()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = Concert::factory()->unpublished()->create($this->oldConcertData($user));
        $this->assertFalse($concert->isPublished());

        // Act
        $response = $this->patch("/backstage/concerts/{$concert->id}", $this->validParams());

        // Assert
        $response->assertRedirect('/login');
        $this->assertRemainTheSame($this->oldConcertData($user), $concert);
    }

    function test_title_is_required()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = Concert::factory()->unpublished()->create([
            'user_id' => $user->id,
            'title' => 'Old title',
        ]);
        $this->assertFalse($concert->isPublished());

        // Act
        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams([
                'title' => '',
            ]));

        // Assert
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('title');
        tap($concert->fresh(), function ($concert) {
            $this->assertEquals('Old title', $concert->title);
        });
    }

    function test_subtitle_is_optional()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = Concert::factory()->unpublished()->create([
            'user_id' => $user->id,
            'subtitle' => 'Old subtitle',
        ]);
        $this->assertFalse($concert->isPublished());

        // Act
        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams([
                'subtitle' => '',
            ]));

        // Assert
        $response->assertRedirect("/backstage/concerts");
        tap($concert->fresh(), function ($concert) {
            $this->assertNull($concert->subtitle);
        });
    }

    function test_additional_information_is_optional()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = Concert::factory()->unpublished()->create([
            'user_id' => $user->id,
            'additional_information' => 'Old additional information',
        ]);
        $this->assertFalse($concert->isPublished());

        // Act
        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams([
                'additional_information' => '',
            ]));

        // Assert
        $response->assertRedirect("/backstage/concerts");
        tap($concert->fresh(), function ($concert) {
            $this->assertNull($concert->additional_information);
        });
    }

    function test_date_is_required()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = Concert::factory()->unpublished()->create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2018-01-01 8:00pm'),
        ]);
        $this->assertFalse($concert->isPublished());

        // Act
        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams([
                'date' => '',
            ]));

        // Assert
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('date');
        tap($concert->fresh(), function ($concert) {
            $this->assertEquals(Carbon::parse('2018-01-01 8:00pm'), $concert->date);
        });
    }

    function test_date_must_be_a_valid_date()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = Concert::factory()->unpublished()->create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2018-01-01 8:00pm'),
        ]);
        $this->assertFalse($concert->isPublished());

        // Act
        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams([
                'date' => 'not a date',
            ]));

        // Assert
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('date');
        tap($concert->fresh(), function ($concert) {
            $this->assertEquals(Carbon::parse('2018-01-01 8:00pm'), $concert->date);
        });
    }

    function test_time_is_required()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = Concert::factory()->unpublished()->create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2018-01-01 8:00pm'),
        ]);
        $this->assertFalse($concert->isPublished());

        // Act
        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams([
                'date' => 'not a date',
            ]));

        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams([
                'time' => '',
            ]));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('time');
        tap($concert->fresh(), function ($concert) {
            $this->assertEquals(Carbon::parse('2018-01-01 8:00pm'), $concert->date);
        });
    }

    function test_time_must_be_a_valid_time()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = Concert::factory()->unpublished()->create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2018-01-01 8:00pm'),
        ]);
        $this->assertFalse($concert->isPublished());

        // Act
        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams([
                'time' => 'not a time',
            ]));

        // Assert
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('time');
        tap($concert->fresh(), function ($concert) {
            $this->assertEquals(Carbon::parse('2018-01-01 8:00pm'), $concert->date);
        });
    }

    function test_venue_is_required()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = Concert::factory()->unpublished()->create([
            'user_id' => $user->id,
            'venue' => 'Old venue',
        ]);
        $this->assertFalse($concert->isPublished());

        // Act
        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams([
                'venue' => '',
            ]));

        // Assert
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('venue');
        tap($concert->fresh(), function ($concert) {
            $this->assertEquals('Old venue', $concert->venue);
        });
    }

    function test_venue_address_is_required()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = Concert::factory()->unpublished()->create([
            'user_id' => $user->id,
            'venue_address' => 'Old address',
        ]);
        $this->assertFalse($concert->isPublished());

        // Act
        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams([
                'venue_address' => '',
            ]));

        // Assert
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('venue_address');
        tap($concert->fresh(), function ($concert) {
            $this->assertEquals('Old address', $concert->venue_address);
        });
    }

    function test_city_is_required()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = Concert::factory()->unpublished()->create([
            'user_id' => $user->id,
            'city' => 'Old city',
        ]);
        $this->assertFalse($concert->isPublished());

        // Act
        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams([
                'city' => '',
            ]));

        // Assert
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('city');
        tap($concert->fresh(), function ($concert) {
            $this->assertEquals('Old city', $concert->city);
        });
    }

    function test_state_is_required()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = Concert::factory()->unpublished()->create([
            'user_id' => $user->id,
            'state' => 'Old state',
        ]);
        $this->assertFalse($concert->isPublished());

        // Act
        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams([
                'state' => '',
            ]));

        // Assert
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('state');
        tap($concert->fresh(), function ($concert) {
            $this->assertEquals('Old state', $concert->state);
        });
    }

    function test_zip_is_required()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = Concert::factory()->unpublished()->create([
            'user_id' => $user->id,
            'zip' => 'Old zip',
        ]);
        $this->assertFalse($concert->isPublished());

        // Act
        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams([
                'zip' => '',
            ]));

        // Assert
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('zip');
        tap($concert->fresh(), function ($concert) {
            $this->assertEquals('Old zip', $concert->zip);
        });
    }

    /** @test */
    function ticket_price_is_required()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = Concert::factory()->unpublished()->create([
            'user_id' => $user->id,
            'ticket_price' => 5250,
        ]);
        $this->assertFalse($concert->isPublished());

        // Act
        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams([
                'ticket_price' => '',
            ]));

        // Assert
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_price');
        tap($concert->fresh(), function ($concert) {
            $this->assertEquals(5250, $concert->ticket_price);
        });
    }

    function test_ticket_price_must_be_numeric()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = Concert::factory()->unpublished()->create([
            'user_id' => $user->id,
            'ticket_price' => 5250,
        ]);
        $this->assertFalse($concert->isPublished());

        // Act
        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams([
                'ticket_price' => 'is not numeric',
            ]));

        // Assert
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_price');
        tap($concert->fresh(), function ($concert) {
            $this->assertEquals(5250, $concert->ticket_price);
        });
    }

    function test_ticket_price_must_be_at_least_5()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = Concert::factory()->unpublished()->create([
            'user_id' => $user->id,
            'ticket_price' => 5250,
        ]);
        $this->assertFalse($concert->isPublished());

        // Act
        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams([
                'ticket_price' => 4,
            ]));

        // Assert
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_price');
        tap($concert->fresh(), function ($concert) {
            $this->assertEquals(5250, $concert->ticket_price);
        });
    }

    function ticket_quantity_is_required()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = Concert::factory()->unpublished()->create([
            'user_id' => $user->id,
            'ticket_quantity' => 5,
        ]);
        $this->assertFalse($concert->isPublished());

        // Act
        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams([
                'ticket_quantity' => '',
            ]));

        // Assert
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_quantity');
        tap($concert->fresh(), function ($concert) {
            $this->assertEquals(5, $concert->ticket_quantity);
        });
    }

    function test_ticket_quantity_must_be_an_integer()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = Concert::factory()->unpublished()->create([
            'user_id' => $user->id,
            'ticket_quantity' => 5,
        ]);
        $this->assertFalse($concert->isPublished());

        // Act
        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams([
                'ticket_quantity' => 7.8,
            ]));

        // Assert
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_quantity');
        tap($concert->fresh(), function ($concert) {
            $this->assertEquals(5, $concert->ticket_quantity);
        });
    }

    function test_ticket_quantity_must_be_at_least_1()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = Concert::factory()->unpublished()->create([
            'user_id' => $user->id,
            'ticket_quantity' => 5,
        ]);
        $this->assertFalse($concert->isPublished());

        // Act
        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams([
                'ticket_quantity' => 0,
            ]));

        // Assert
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_quantity');
        tap($concert->fresh(), function ($concert) {
            $this->assertEquals(5, $concert->ticket_quantity);
        });
    }
}

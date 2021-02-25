<?php

namespace Tests\Feature\Backstage;

use App\Models\Concert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EditConcertTest extends TestCase
{
    use RefreshDatabase;

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
        $concert = Concert::factory()->published()->create(['user_id' => $user->id]);
        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(403);
    }

    function test_promoters_cannot_view_the_edit_form_for_other_concerts()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $concertOfOther = Concert::factory()->create(['user_id' => $otherUser->id]);

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
}

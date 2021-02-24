<?php

namespace Tests\Feature\Backstage;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AddConcertTest extends TestCase
{
    use RefreshDatabase;

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
}

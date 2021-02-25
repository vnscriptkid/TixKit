<?php

namespace Tests\Feature\Backstage;

use App\Models\Concert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ViewConcertList extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_not_view_promoters_concert_list()
    {
        $response = $this->get('/backstage/concerts');

        $response->assertRedirect('/login');
    }

    public function test_promoters_can_view_his_concert_list()
    {
        $this->withoutExceptionHandling();
        // Arrange
        $user = User::factory()->create();
        $concerts = Concert::factory(3)->create(['user_id' => $user->id]);

        // Act
        $response = $this->actingAs($user)->get('/backstage/concerts');

        // Assert
        $response->assertStatus(200);

        $concertsInView = $response->original->getData()['concerts'];
        $this->assertTrue($concertsInView->contains($concerts[0]));
        $this->assertTrue($concertsInView->contains($concerts[1]));
        $this->assertTrue($concertsInView->contains($concerts[2]));
        $this->assertCount(3, $concertsInView);
    }
}

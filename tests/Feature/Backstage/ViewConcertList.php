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

    public function test_promoters_can_view_his_concert_list_but_not_others()
    {
        $this->withoutExceptionHandling();
        // Arrange
        $me = User::factory()->create();
        $otherUser = User::factory()->create();

        $concertA = Concert::factory()->create(['user_id' => $me->id]);
        $concertB = Concert::factory()->create(['user_id' => $otherUser->id]);
        $concertC = Concert::factory()->create(['user_id' => $otherUser->id]);
        $concertD = Concert::factory()->create(['user_id' => $me->id]);
        $concertE = Concert::factory()->create(['user_id' => $me->id]);

        // Act
        $response = $this->actingAs($me)->get('/backstage/concerts');

        // Assert
        $response->assertStatus(200);

        $concertsInView = $response->original->getData()['concerts'];

        $this->assertCount(3, $concertsInView);
        $this->assertTrue($concertsInView->contains($concertA));
        $this->assertTrue($concertsInView->contains($concertD));
        $this->assertTrue($concertsInView->contains($concertE));

        $this->assertFalse($concertsInView->contains($concertB));
        $this->assertFalse($concertsInView->contains($concertC));
    }
}

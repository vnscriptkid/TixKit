<?php

namespace Tests\Feature\Backstage;

use App\Models\Concert;
use App\Models\User;
use Database\Factories\ConcertFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishConcertTest extends TestCase
{
    use RefreshDatabase;

    public function test_publish_my_own_unpublished_concert_successfully()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = ConcertFactory::createUnpublished([
            'ticket_quantity' => 10,
            'user_id' => $user->id
        ]);
        $this->assertEquals(0, $concert->ticketsRemaining());
        $this->assertFalse($concert->isPublished());

        // Act
        $response = $this->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => $concert->id
        ]);

        // Assert
        $response->assertRedirect('/backstage/concerts');
        $this->assertEquals(10, $concert->ticketsRemaining());
        $this->assertTrue($concert->fresh()->isPublished());
    }

    public function test_publish_an_unpublished_concert_of_other_should_return_404()
    {
        // Arrange
        $me = User::factory()->create();
        $otherUser = User::factory()->create();
        $concertOfOther = ConcertFactory::createUnpublished([
            'ticket_quantity' => 10,
            'user_id' => $otherUser->id
        ]);
        $this->assertEquals(0, $concertOfOther->ticketsRemaining());
        $this->assertFalse($concertOfOther->isPublished());

        // Act
        $response = $this->actingAs($me)->post('/backstage/published-concerts', [
            'concert_id' => $concertOfOther->id
        ]);

        // Assert
        $response->assertStatus(404);
        $this->assertEquals(0, $concertOfOther->ticketsRemaining());
        $this->assertFalse($concertOfOther->fresh()->isPublished());
    }

    public function test_publish_a_published_concert_of_other_should_return_404()
    {
        // Arrange
        $me = User::factory()->create();
        $otherUser = User::factory()->create();
        $concertOfOther = ConcertFactory::createPublished([
            'ticket_quantity' => 10,
            'user_id' => $otherUser->id
        ]);
        $this->assertEquals(10, $concertOfOther->ticketsRemaining());
        $this->assertTrue($concertOfOther->isPublished());

        // Act
        $response = $this->actingAs($me)->post('/backstage/published-concerts', [
            'concert_id' => $concertOfOther->id
        ]);

        // Assert
        $response->assertStatus(404);
        $this->assertEquals(10, $concertOfOther->ticketsRemaining());
        $this->assertTrue($concertOfOther->fresh()->isPublished());
    }

    public function test_concert_is_only_published_once()
    {
        // Arrange
        $me = User::factory()->create();
        $concertOfOther = ConcertFactory::createPublished([
            'ticket_quantity' => 10,
            'user_id' => $me->id
        ]);
        $this->assertEquals(10, $concertOfOther->ticketsRemaining());
        $this->assertTrue($concertOfOther->isPublished());

        // Act
        $response = $this->actingAs($me)->post('/backstage/published-concerts', [
            'concert_id' => $concertOfOther->id
        ]);

        // Assert
        $response->assertStatus(302);
        $this->assertEquals(10, $concertOfOther->ticketsRemaining());
        $this->assertTrue($concertOfOther->fresh()->isPublished());
    }

    public function test_id_is_required_in_body()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = ConcertFactory::createUnpublished([
            'ticket_quantity' => 10,
            'user_id' => $user->id
        ]);
        $this->assertEquals(0, $concert->ticketsRemaining());
        $this->assertFalse($concert->isPublished());

        // Act
        $response = $this->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => ''
        ]);

        // Assert
        $response->assertSessionHasErrors(['concert_id']);
        $this->assertFalse($concert->fresh()->isPublished());
    }

    public function test_publish_a_nonexistent_concert_should_return_404()
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => 999
        ]);

        // Assert
        $response->assertStatus(404);
    }

    public function test_guest_can_not_publish_a_concert()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = ConcertFactory::createUnpublished([
            'ticket_quantity' => 10,
            'user_id' => $user->id
        ]);
        $this->assertEquals(0, $concert->ticketsRemaining());
        $this->assertFalse($concert->isPublished());

        // Act
        $response = $this->post('/backstage/published-concerts', [
            'concert_id' => $concert->id
        ]);

        // Assert
        $response->assertRedirect('/login');
        $this->assertEquals(0, $concert->ticketsRemaining());
        $this->assertFalse($concert->isPublished());
    }
}

<?php

namespace Tests\Feature\Backstage;

use App\Jobs\SendAttendeeMessage;
use App\Models\AttendeeMessage;
use App\Models\User;
use Database\Factories\ConcertFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MessageAttendeesTest extends TestCase
{
    use RefreshDatabase;

    function test_a_promoter_can_view_the_message_form_for_their_own_concert()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = ConcertFactory::createPublished([
            'user_id' => $user->id,
        ]);

        // Act
        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/messages/new");

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('backstage.concert-messages.new');
        $this->assertTrue($response->viewData('concert')->is($concert));
    }

    function test_a_promoter_cannot_view_the_message_form_for_another_concert()
    {
        // Arrange
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $concertOfAnother = ConcertFactory::createPublished([
            'user_id' => $anotherUser->id
        ]);

        // Act
        $response = $this->actingAs($user)->get("/backstage/concerts/{$concertOfAnother->id}/messages/new");

        // Assert
        $response->assertStatus(404);
    }

    function test_a_promoter_can_send_a_new_message()
    {
        // Arrange
        $user = User::factory()->create();
        $concert = ConcertFactory::createPublished([
            'user_id' => $user->id,
        ]);
        Queue::fake();

        // Act
        $response = $this->actingAs($user)->post("/backstage/concerts/{$concert->id}/messages", [
            'subject' => 'My subject',
            'message' => 'My message',
        ]);

        // Assert
        $response->assertRedirect("/backstage/concerts/{$concert->id}/messages/new");
        $response->assertSessionHas('flash');

        $message = AttendeeMessage::first();
        $this->assertEquals($concert->id, $message->concert_id);
        $this->assertEquals('My subject', $message->subject);
        $this->assertEquals('My message', $message->message);
        Queue::assertPushed(SendAttendeeMessage::class, function ($job) use ($message) {
            return $message->is($job->attendeeMessage);
        });
    }

    function test_a_promoter_cannot_send_a_new_message_for_other_concerts()
    {
        // Arrange
        Queue::fake();
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $concertOfOther = ConcertFactory::createPublished([
            'user_id' => $otherUser->id,
        ]);

        // Act
        $response = $this->actingAs($user)->post("/backstage/concerts/{$concertOfOther->id}/messages", [
            'subject' => 'My subject',
            'message' => 'My message',
        ]);

        // Assert
        $response->assertStatus(404);
        $this->assertEquals(0, AttendeeMessage::count());
        Queue::assertNotPushed(SendAttendeeMessage::class);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AcceptInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewing_an_unused_invitation()
    {
        // Arrange
        $invitation = Invitation::factory()->create([
            'code' => 'INVITATIONCODE123',
            'user_id' => null
        ]);

        // Act
        $response = $this->get('/invitations/' . $invitation->code);

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('invitations.show');
        $this->assertTrue($invitation->is($response->viewData('invitation')));
    }

    public function test_viewing_an_used_invitation()
    {
        // Arrange
        $invitation = Invitation::factory()->create([
            'code' => 'INVITATIONCODE123',
            'user_id' => User::factory()->create()->id
        ]);

        // Act
        $response = $this->get('/invitations/' . $invitation->code);

        // Assert
        $response->assertStatus(404);
    }

    public function test_view_nonexistent_invitation()
    {
        // Act
        $response = $this->get('/invitations/' . 'FAKECODE');

        // Assert
        $response->assertStatus(404);
    }
}

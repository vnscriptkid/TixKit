<?php

namespace Tests\Feature;

use App\Models\Invitation;
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
            'code' => 'INVITATIONCODE123'
        ]);

        // Act
        $response = $this->get('/invitations/' . $invitation->code);

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('invitations.show');
        $this->assertTrue($invitation->is($response->viewData('invitation')));
    }
}

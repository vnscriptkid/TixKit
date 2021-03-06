<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
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

    public function test_regsitering_user_with_valid_code()
    {
        // Arrange
        $invitation = Invitation::factory()->create([
            'code' => 'INVITATIONCODE123',
            'user_id' => null
        ]);

        // Act
        $response = $this->post('/register', [
            'email' => 'tom@gmail.com',
            'password' => '123456',
            'invitation_code' => 'INVITATIONCODE123'
        ]);

        // Assert
        $response->assertRedirect('/backstage/concerts');
        $user = User::firstOrFail();
        $this->assertEquals(1, User::count());
        $this->assertAuthenticatedAs($user);
        $this->assertEquals($user->email, 'tom@gmail.com');
        $this->assertTrue(Hash::check('123456', $user->password));
        $this->assertTrue($invitation->fresh()->hasBeenUsed());
        $this->assertTrue($invitation->fresh()->user->is($user));
    }

    public function test_registering_with_invalid_code_should_return_404()
    {
        // Act
        $response = $this->post('/register', [
            'email' => 'tom@gmail.com',
            'password' => '123456',
            'invitation_code' => 'FAKECODE'
        ]);

        // Assert
        $response->assertStatus(404);
        $this->assertEquals(0, User::count());
    }

    public function test_registering_user_using_used_code()
    {
        // Arrange
        Invitation::factory()->create([
            'code' => 'INVITATIONCODE123',
            'user_id' => User::factory()->create()->id
        ]);

        $this->assertEquals(1, User::count());

        // Act
        $response = $this->post('/register', [
            'email' => 'tom@gmail.com',
            'password' => '123456',
            'invitation_code' => 'INVITATIONCODE123'
        ]);

        // Assert
        $response->assertStatus(404);
        $this->assertEquals(1, User::count());
    }
}

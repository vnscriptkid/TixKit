<?php

namespace Tests\Unit\Mail;

use App\Mail\InvitationEmail;
use App\Models\Invitation;
use Tests\TestCase;

class InvitationEmailTest extends TestCase
{
    public function test_it_contains_link_to_invitation()
    {
        $invitation = Invitation::factory()->make([
            'email' => 'david@gmail.com',
            'code' => 'CODEFORDAVID'
        ]);

        $email = new InvitationEmail($invitation);

        $this->assertStringContainsString(url('/invitations/CODEFORDAVID'), $email->render());
    }

    public function test_it_has_correct_subject()
    {
        $invitation = Invitation::factory()->make([
            'email' => 'david@gmail.com',
            'code' => 'CODEFORDAVID'
        ]);

        $email = new InvitationEmail($invitation);

        $this->assertEquals($email->build()->subject, 'You are invited to join #TixKit');
    }
}

<?php

namespace Tests\Feature;

use App\Facades\InvitationCode;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InvitePromoterTest extends TestCase
{
    use RefreshDatabase;

    public function test_inviting_a_promoter_via_cli()
    {
        InvitationCode::shouldReceive('generate')->andReturn('INVITATIONCODE123');
        $this->artisan('invite-promoter', ['email' => 'john@gmail.com']);

        $this->assertEquals(1, Invitation::count());
        tap(Invitation::first(), function ($invitation) {
            $this->assertEquals('INVITATIONCODE123', $invitation->code);
            $this->assertEquals('john@gmail.com', $invitation->email);
        });
    }
}

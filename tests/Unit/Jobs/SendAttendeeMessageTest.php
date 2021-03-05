<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendAttendeeMessage;
use App\Mail\AttendeeMessageEmail;
use App\Models\AttendeeMessage;
use App\Models\Concert;
use App\Models\Order;
use App\Models\User;
use Database\Factories\OrderFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SendAttendeeMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_send_the_message_to_all_attendees()
    {
        // Assert
        Mail::fake();
        $me = User::factory()->create();
        $someoneElse = User::factory()->create();

        $myConcert = Concert::factory()->create(['user_id' => $me->id]);
        $myOtherConcert = Concert::factory()->create(['user_id' => $me->id]);
        $someoneElsesConcert = Concert::factory()->create(['user_id' => $someoneElse->id]);

        $orderA = OrderFactory::createForConcert($myConcert, ['email' => 'userA@gmail.com']);
        $orderB = OrderFactory::createForConcert($myOtherConcert, ['email' => 'userB@gmail.com']);
        $orderC = OrderFactory::createForConcert($myConcert, ['email' => 'userC@gmail.com']);
        $orderD = OrderFactory::createForConcert($someoneElsesConcert, ['email' => 'userD@gmail.com']);
        $message = AttendeeMessage::create([
            'concert_id' => $myConcert->id,
            'subject' => 'Example Subject',
            'message' => 'Example Message'
        ]);

        // Act
        SendAttendeeMessage::dispatch($message); // call handle() internally

        // Assert
        Mail::assertQueued(AttendeeMessageEmail::class, function ($mail) use ($message) {
            return $mail->hasTo('userA@gmail.com') && $message->is($mail->attendeeMessage);
        });

        Mail::assertQueued(AttendeeMessageEmail::class, function ($mail) use ($message) {
            return $mail->hasTo('userC@gmail.com') && $message->is($mail->attendeeMessage);
        });

        Mail::assertNotQueued(AttendeeMessageEmail::class, function ($mail) {
            return $mail->hasTo('userB@gmail.com');
        });

        Mail::assertNotQueued(AttendeeMessageEmail::class, function ($mail) {
            return $mail->hasTo('userD@gmail.com');
        });
    }
}

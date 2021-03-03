<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendAttendeeMessage;
use App\Mail\AttendeeMessageEmail;
use App\Models\AttendeeMessage;
use App\Models\Concert;
use App\Models\Order;
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
        $this->withoutExceptionHandling();
        // Assert
        Mail::fake();
        $concert = Concert::factory()->create([]);
        $otherConcert = Concert::factory()->create([]);
        $orderA = OrderFactory::createForConcert($concert, ['email' => 'userA@gmail.com']);
        $orderB = OrderFactory::createForConcert($concert, ['email' => 'userB@gmail.com']);
        $orderC = OrderFactory::createForConcert($otherConcert, ['email' => 'userC@gmail.com']);
        $message = AttendeeMessage::create([
            'concert_id' => $concert->id,
            'subject' => 'Example Subject',
            'message' => 'Example Message'
        ]);

        // Act
        SendAttendeeMessage::dispatch($message); // call handle() internally

        // Assert
        Mail::assertSent(AttendeeMessageEmail::class, function ($mail) use ($message) {
            return $mail->hasTo('userA@gmail.com') && $message->is($mail->attendeeMessage);
        });

        Mail::assertSent(AttendeeMessageEmail::class, function ($mail) use ($message) {
            return $mail->hasTo('userB@gmail.com') && $message->is($mail->attendeeMessage);
        });

        Mail::assertNotSent(AttendeeMessageEmail::class, function ($mail) {
            return $mail->hasTo('userC@gmail.com');
        });
    }
}

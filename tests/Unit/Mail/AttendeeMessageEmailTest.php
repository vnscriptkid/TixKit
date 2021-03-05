<?php

namespace Tests\Unit\Mail;

use App\Mail\AttendeeMessageEmail;
use App\Models\AttendeeMessage;
use Tests\TestCase;

class AttendeeMessageEmailTest extends TestCase
{
    public function test_email_has_the_correct_subject_and_message()
    {
        $message = new AttendeeMessage([
            'subject' => 'Test subject',
            'message' => 'Test message'
        ]);

        $email = new AttendeeMessageEmail($message);

        $this->assertEquals($email->build()->subject, 'Test subject');
        $this->assertEquals(trim($email->render()), 'Test message');
    }
}

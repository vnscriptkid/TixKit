<?php

namespace App\Jobs;

use App\Mail\AttendeeMessageEmail;
use App\Models\AttendeeMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAttendeeMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $attendeeMessage;

    public function __construct(AttendeeMessage $attendeeMessage)
    {
        $this->attendeeMessage = $attendeeMessage;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // $this->attendeeMessage->concert->recipients()->each(function ($recipient) {
        //     Mail::to($recipient)->queue(new AttendeeMessageEmail($this->attendeeMessage));
        // });

        // $this->attendeeMessage->orders()->chunk(100, function ($orders) {
        //     $orders->pluck('email')->each(function ($recipient) {
        //         Mail::to($recipient)->queue(new AttendeeMessageEmail($this->attendeeMessage));
        //     });
        // });

        $this->attendeeMessage->withChunkedRecipients(100, function ($recipients) {
            $recipients->each(function ($recipient) {
                Mail::to($recipient)->queue(new AttendeeMessageEmail($this->attendeeMessage));
            });
        });
    }
}

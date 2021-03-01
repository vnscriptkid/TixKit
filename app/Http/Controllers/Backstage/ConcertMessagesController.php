<?php

namespace App\Http\Controllers\Backstage;

use App\Http\Controllers\Controller;
use App\Jobs\SendAttendeeMessage;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConcertMessagesController extends Controller
{
    public function create($id)
    {
        return view('backstage.concert-messages.new', [
            'concert' => Auth::user()->concerts()->findOrFail($id)
        ]);
    }

    public function store($id)
    {
        request()->validate([
            'subject' => ['required'],
            'message' => ['required']
        ]);

        $concert = Auth::user()->concerts()->findOrFail($id);

        $message = $concert->attendeeMessages()->create(request(['subject', 'message']));

        SendAttendeeMessage::dispatch($message);

        return redirect()->route('backstage.concert-messages.new', $concert)->with('flash', 'Your message has been sent.');
    }
}

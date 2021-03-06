<?php

use App\Facades\InvitationCode;
use App\Models\Invitation;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('invite-promoter {email}', function ($email) {
    Invitation::create([
        'code' => InvitationCode::generate(),
        'email' => $email
    ]);
})->purpose('Invite a promoter to create an account.');

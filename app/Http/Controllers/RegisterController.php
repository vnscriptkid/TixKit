<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function register()
    {
        $invitation = Invitation::findByCode(request('invitation_code'));

        abort_if($invitation->hasBeenUsed(), 404);

        $user = User::create([
            'email' => request('email'),
            'password' => bcrypt(request('password'))
        ]);

        $invitation->update([
            'user_id' => $user->id
        ]);

        Auth::login($user);

        return redirect()->route('backstage.concerts.index');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StripeConnectController extends Controller
{
    public function authorizeRedirect()
    {
        $url = vsprintf('%s?%s', [
            'https://connect.stripe.com/oauth/authorize',
            http_build_query([
                'response_type' => 'code',
                'scope' => 'read_write',
                'client_id' => config('services.stripe.client_id')
            ])
        ]);

        return redirect($url);
    }

    public function redirect()
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $response = \Stripe\OAuth::token([
            'grant_type' => 'authorization_code',
            'code' => request('code'),
        ]);

        Auth::user()->update([
            'stripe_account_id' => $response['stripe_user_id'],
            'stripe_access_token' => $response['access_token']
        ]);

        return redirect()->route('backstage.concerts.index');
    }

    public function connect()
    {
        return view('backstage.stripe-connect.connect');
    }
}

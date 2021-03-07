<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
}

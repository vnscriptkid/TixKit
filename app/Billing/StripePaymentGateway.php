<?php

namespace App\Billing;

class StripePaymentGateway implements PaymentGateway
{
    private $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function charge($amount, $token)
    {
        $stripeCharge = \Stripe\Charge::create([
            'amount' => $amount,
            'source' => $token,
            'currency' => 'usd',
        ], ['api_key' => $this->apiKey]);
    }
}

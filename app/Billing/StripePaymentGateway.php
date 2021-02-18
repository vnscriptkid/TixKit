<?php

namespace App\Billing;

use Stripe\Exception\InvalidRequestException;

class StripePaymentGateway implements PaymentGateway
{
    private $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function charge($amount, $token)
    {
        try {
            $stripeCharge = \Stripe\Charge::create([
                'amount' => $amount,
                'source' => $token,
                'currency' => 'usd',
            ], ['api_key' => $this->apiKey]);
        } catch (InvalidRequestException $e) {
            throw new PaymentFailedException;
        }
    }

    public function getValidToken()
    {
        return \Stripe\Token::create([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 1,
                'exp_year' => date('Y') + 1,
                'cvc' => '123',
            ],
        ], ['api_key' => config('services.stripe.secret')])->id;
    }

    public function getNewChargesDuring($callback)
    {
        $lastCharge = $this->getLastCharge();

        $callback->__invoke($this);

        $newCharges = $this->getNewChargesSince($lastCharge);

        return collect($newCharges)->pluck('amount');
    }

    private function getLastCharge()
    {
        return \Stripe\Charge::all(['limit' => 1], ['api_key' => $this->apiKey])['data'][0];
    }

    private function getNewChargesSince($lastCharge)
    {
        return \Stripe\Charge::all(
            ['limit' => 1, 'ending_before' => $lastCharge->id],
            ['api_key' => config('services.stripe.secret')]
        )['data'];
    }
}

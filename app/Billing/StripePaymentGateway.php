<?php

namespace App\Billing;

use Stripe\Exception\InvalidRequestException;

class StripePaymentGateway implements PaymentGateway
{
    private $apiKey;
    const TEST_CARD_NUMBER = '4242424242424242';

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function charge($amount, $token, $destinationAccountId)
    {
        try {
            $charge = \Stripe\Charge::create([
                'amount' => $amount,
                'source' => $token,
                'currency' => 'usd',
                'destination' => [
                    'account' => $destinationAccountId,
                    'amount' => $amount * .9
                ]
            ], ['api_key' => $this->apiKey]);

            return new Charge([
                'amount' => $charge['amount'],
                'card_last_four' => $charge['source']['last4'],
                'destination' => $destinationAccountId
            ]);
        } catch (InvalidRequestException $e) {
            throw new PaymentFailedException;
        }
    }

    public function getValidTestToken($cardNumber = self::TEST_CARD_NUMBER)
    {
        return \Stripe\Token::create([
            'card' => [
                'number' => $cardNumber,
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

        return collect($newCharges)->map(function ($charge) {
            return new Charge([
                'amount' => $charge['amount'],
                'card_last_four' => $charge['source']['last4']
            ]);
        });
    }

    private function getLastCharge()
    {
        return \Stripe\Charge::all(['limit' => 1], ['api_key' => $this->apiKey])['data'][0];
    }

    private function getNewChargesSince($lastCharge)
    {
        return \Stripe\Charge::all(
            ['ending_before' => $lastCharge->id],
            ['api_key' => config('services.stripe.secret')]
        )['data'];
    }
}

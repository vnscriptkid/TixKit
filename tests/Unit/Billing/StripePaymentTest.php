<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use App\Billing\StripePaymentGateway;
use Tests\TestCase;

class StripePaymentTest extends TestCase
{
    public function test_charges_with_valid_token_are_successful()
    {
        $lastCharge = \Stripe\Charge::all(['limit' => 1], ['api_key' => config('services.stripe.secret')])['data'][0];

        $token = \Stripe\Token::create([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 1,
                'exp_year' => date('Y') + 1,
                'cvc' => '123',
            ],
        ], ['api_key' => config('services.stripe.secret')])->id;

        $paymentGateway = new StripePaymentGateway(config('services.stripe.secret'));

        $paymentGateway->charge(1200, $token);

        $newCharge = \Stripe\Charge::all(
            ['limit' => 1, 'ending_before' => $lastCharge->id],
            ['api_key' => config('services.stripe.secret')]
        )['data'][0];

        $this->assertEquals($newCharge['amount'], 1200);
    }
}

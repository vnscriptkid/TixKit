<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use Tests\TestCase;

class StripePaymentTest extends TestCase
{
    public function test_charges_with_valid_token_are_successful()
    {
        $token = \Stripe\Token::create([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 1,
                'exp_year' => date('Y') + 1,
                'cvc' => '123',
            ],
        ], ['api_key' => config('services.stripe.secret')])->id;

        dd($token);
        // $paymentGateway = new FakePaymentGateway();

        // $paymentGateway->charge(1200, $paymentGateway->getValidTestToken());

        // $this->assertEquals($paymentGateway->totalCharges(), 1200);
    }
}

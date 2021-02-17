<?php

namespace Tests\External;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
use App\Billing\StripePaymentGateway;
use Tests\TestCase;

class StripePaymentTest extends TestCase
{
    private $paymentGateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentGateway = new StripePaymentGateway(config('services.stripe.secret'));
    }

    private function validToken()
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

    private function lastCharge()
    {
        return \Stripe\Charge::all(['limit' => 1], ['api_key' => config('services.stripe.secret')])['data'][0];
    }

    private function newerChargesThan($lastCharge)
    {
        $newCharges = \Stripe\Charge::all(
            ['limit' => 1, 'ending_before' => $lastCharge->id],
            ['api_key' => config('services.stripe.secret')]
        )['data'];

        return collect($newCharges);
    }

    public function test_charges_with_valid_token_are_successful()
    {
        $lastCharge = $this->lastCharge();

        $this->paymentGateway->charge(1200, $this->validToken());

        $newerCharges = $this->newerChargesThan($lastCharge);

        $this->assertCount(1, $newerCharges);
        $this->assertEquals($newerCharges->first()['amount'], 1200);
    }

    public function test_charges_with_invalid_token_fail()
    {
        $lastCharge = $this->lastCharge();

        try {
            $this->paymentGateway->charge(1200, 'invalid-payment-token');
        } catch (PaymentFailedException $e) {
            $newerCharges = $this->newerChargesThan($lastCharge);
            $this->assertCount(0, $newerCharges);
            return;
        }
        $this->fail("Charging succeeded even though invalid token was sent to Stripe.");
    }
}

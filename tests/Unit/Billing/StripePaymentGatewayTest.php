<?php

namespace Tests\External;

use App\Billing\StripePaymentGateway;
use Tests\TestCase;
use Tests\Unit\Billing\PaymentGatewayContractTests;

class StripePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTests;

    protected function getPaymentGateway()
    {
        return new StripePaymentGateway(config('services.stripe.secret'));
    }

    public function test_90_percent_of_charge_is_transfered_to_promoter_account()
    {
        // Arrange
        $paymentGateway = $this->getPaymentGateway();

        // Act
        $paymentGateway->charge(5000, $paymentGateway->getValidTestToken(), env('PROMOTER_STRIPE_ACCOUNT_ID'));

        // Assert
        $lastCharge = \Stripe\Charge::all(['limit' => 1], ['api_key' => config('services.stripe.secret')])['data'][0];
        $this->assertEquals(5000, $lastCharge['amount']);
        $this->assertEquals(env('PROMOTER_STRIPE_ACCOUNT_ID'), $lastCharge['destination']);
        $transfer = \Stripe\Transfer::retrieve($lastCharge['transfer'], ['api_key' => config('services.stripe.secret')]);
        $this->assertEquals(4500, $transfer['amount']);
    }
}

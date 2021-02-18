<?php

namespace Tests\Unit\Billing;

use App\Billing\PaymentFailedException;

trait PaymentGatewayContractTests
{
    abstract protected function getPaymentGateway();

    public function test_charges_with_valid_token_are_successful()
    {
        $paymentGateway = $this->getPaymentGateway();

        $newCharges = $paymentGateway->getNewChargesDuring(function ($gateway) {
            $gateway->charge(1200, $gateway->getValidToken());
        });

        $this->assertCount(1, $newCharges);
        $this->assertEquals($newCharges->first(), 1200);
    }

    public function test_charges_with_invalid_token_fail()
    {
        $paymentGateway = $this->getPaymentGateway();

        $newCharges = $paymentGateway->getNewChargesDuring(function ($gateway) {
            try {
                $gateway->charge(1200, 'invalid-payment-token');
            } catch (PaymentFailedException $e) {
                return;
            }
            $this->fail("Charging succeeded even though invalid token was sent to Stripe.");
        });

        $this->assertCount(0, $newCharges);
    }
}

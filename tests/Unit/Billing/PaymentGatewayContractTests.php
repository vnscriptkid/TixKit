<?php

namespace Tests\Unit\Billing;

use App\Billing\PaymentFailedException;

trait PaymentGatewayContractTests
{
    abstract protected function getPaymentGateway();

    public function test_fetch_charges_created_during_a_callback()
    {
        $paymentGateway = $this->getPaymentGateway();

        $paymentGateway->charge(1000, $paymentGateway->getValidToken());
        $paymentGateway->charge(2000, $paymentGateway->getValidToken());

        $newCharges = $paymentGateway->getNewChargesDuring(function ($gateway) {
            $gateway->charge(1200, $gateway->getValidToken());
            $gateway->charge(3000, $gateway->getValidToken());
        });

        $this->assertCount(2, $newCharges);
        $this->assertEquals([3000, 1200], $newCharges->all());
    }

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
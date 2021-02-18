<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use PHPUnit\Framework\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTests;

    protected function getPaymentGateway()
    {
        return new FakePaymentGateway();
    }

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
        $this->assertEquals([1200, 3000], $newCharges->all());
    }

    public function test_hook_that_runs_before_first_charge()
    {
        $callbackRan = 0;
        $paymentGateway = $this->getPaymentGateway();

        $paymentGateway->beforeFirstCharge(function ($paymentGateway) use (&$callbackRan) {
            $callbackRan++;
            $paymentGateway->charge(1000, $paymentGateway->getValidToken());
            $this->assertEquals($paymentGateway->totalCharges(), 1000);
        });

        $paymentGateway->charge(1000, $paymentGateway->getValidToken());

        $this->assertEquals($paymentGateway->totalCharges(), 2000);
        $this->assertEquals($callbackRan, 1);
    }
}

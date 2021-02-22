<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use Tests\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTests;

    protected function getPaymentGateway()
    {
        return new FakePaymentGateway();
    }

    public function test_hook_that_runs_before_first_charge()
    {
        $callbackRan = 0;
        $paymentGateway = $this->getPaymentGateway();

        $paymentGateway->beforeFirstCharge(function ($paymentGateway) use (&$callbackRan) {
            $callbackRan++;
            $paymentGateway->charge(1000, $paymentGateway->getValidTestToken());
            $this->assertEquals($paymentGateway->totalCharges(), 1000);
        });

        $paymentGateway->charge(1000, $paymentGateway->getValidTestToken());

        $this->assertEquals($paymentGateway->totalCharges(), 2000);
        $this->assertEquals($callbackRan, 1);
    }
}

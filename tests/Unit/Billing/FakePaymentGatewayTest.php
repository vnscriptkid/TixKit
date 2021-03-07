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

    public function test_can_get_total_charges_for_account()
    {
        // Arrange
        $paymentGateway = $this->getPaymentGateway();
        $paymentGateway->charge(1000, $paymentGateway->getValidTestToken(), 'test-acc-1');
        $paymentGateway->charge(2000, $paymentGateway->getValidTestToken(), 'test-acc-2');
        $paymentGateway->charge(3000, $paymentGateway->getValidTestToken(), 'test-acc-1');

        // Act + Assert
        $this->assertEquals(4000, $paymentGateway->totalChargesFor('test-acc-1'));
    }

    public function test_hook_that_runs_before_first_charge()
    {
        $callbackRan = 0;
        $paymentGateway = $this->getPaymentGateway();

        $paymentGateway->beforeFirstCharge(function ($paymentGateway) use (&$callbackRan) {
            $callbackRan++;
            $paymentGateway->charge(1000, $paymentGateway->getValidTestToken(), 'test-acc-123');
            $this->assertEquals($paymentGateway->totalCharges(), 1000);
        });

        $paymentGateway->charge(1000, $paymentGateway->getValidTestToken(), 'test-acc-123');

        $this->assertEquals($paymentGateway->totalCharges(), 2000);
        $this->assertEquals($callbackRan, 1);
    }
}

<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
use PHPUnit\Framework\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    public function test_charges_with_valid_token_are_successful()
    {
        $paymentGateway = new FakePaymentGateway();

        $paymentGateway->charge(1200, $paymentGateway->getValidTestToken());

        $this->assertEquals($paymentGateway->totalCharges(), 1200);
    }

    public function test_charges_with_invalid_token_fail()
    {
        $paymentGateway = new FakePaymentGateway();

        try {
            $paymentGateway->charge(1200, 'invalid-payment-token');
        } catch (PaymentFailedException $e) {
            $this->assertNotNull($e);
            return;
        }
        $this->fail();
    }

    public function test_hook_that_runs_before_first_charge()
    {
        $paymentGateway = new FakePaymentGateway();
        $callbackRan = 0;

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

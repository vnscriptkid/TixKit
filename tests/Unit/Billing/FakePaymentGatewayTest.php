<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use PHPUnit\Framework\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    public function test_charges_with_valid_token_are_successful()
    {
        $paymentGateway = new FakePaymentGateway();

        $paymentGateway->charge(1200, $paymentGateway->getValidTestToken());

        $this->assertEquals($paymentGateway->totalCharges(), 1200);
    }
}

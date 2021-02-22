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
}

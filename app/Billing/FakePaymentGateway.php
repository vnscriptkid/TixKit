<?php

namespace App\Billing;

class FakePaymentGateway implements PaymentGateway
{
    private $charges;
    private $beforeFirstChargeCallback;

    public function __construct()
    {
        $this->charges = collect();
    }

    public function getValidToken()
    {
        return 'good token';
    }

    public function charge($amount, $token)
    {
        if ($this->beforeFirstChargeCallback !== null) {
            $cb = $this->beforeFirstChargeCallback;
            $this->beforeFirstChargeCallback = null;
            $cb->__invoke($this);
        }

        if ($token !== $this->getValidToken()) {
            throw new PaymentFailedException;
        }

        $this->charges[] = $amount;
    }

    public function getNewChargesDuring($callback)
    {
        $currentSize = $this->charges->count();

        $callback->__invoke($this);

        return $this->charges->skip($currentSize)->reverse()->values();
    }

    public function totalCharges()
    {
        return $this->charges->sum();
    }

    public function beforeFirstCharge(callable $callback)
    {
        $this->beforeFirstChargeCallback = $callback;
    }
}

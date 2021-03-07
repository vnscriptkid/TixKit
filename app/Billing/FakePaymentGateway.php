<?php

namespace App\Billing;

use Illuminate\Support\Str;

class FakePaymentGateway implements PaymentGateway
{
    const TEST_CARD_NUMBER = '4242424242424242';

    private $charges;
    private $tokens;
    private $beforeFirstChargeCallback;

    public function __construct()
    {
        $this->charges = collect();
        $this->tokens = collect();
    }

    public function getValidTestToken($cardNumber = self::TEST_CARD_NUMBER)
    {
        $token = 'fake-tok_' . Str::random(24);
        $this->tokens[$token] = $cardNumber;
        return $token;
    }

    public function charge($amount, $token, $destinationAccountId)
    {
        if ($this->beforeFirstChargeCallback !== null) {
            $cb = $this->beforeFirstChargeCallback;
            $this->beforeFirstChargeCallback = null;
            $cb->__invoke($this);
        }

        if (!$this->tokens->has($token)) {
            throw new PaymentFailedException;
        }

        return $this->charges[] = new Charge([
            'amount' => $amount,
            'card_last_four' => substr($this->tokens[$token], -4),
            'destination' => $destinationAccountId
        ]);
    }

    public function getNewChargesDuring($callback)
    {
        $currentSize = $this->charges->count();

        $callback->__invoke($this);

        return $this->charges->skip($currentSize)->reverse()->values();
    }

    public function totalCharges()
    {
        return $this->charges->map->amount()->sum();
    }

    public function totalChargesFor($stripeAccountId)
    {
        return $this->charges->filter(function ($charge) use ($stripeAccountId) {
            return $charge->destination() === $stripeAccountId;
        })->map->amount()->sum();
    }

    public function beforeFirstCharge(callable $callback)
    {
        $this->beforeFirstChargeCallback = $callback;
    }
}

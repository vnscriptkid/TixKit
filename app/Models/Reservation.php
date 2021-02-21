<?php

namespace App\Models;

use App\Billing\PaymentGateway;

class Reservation
{
    private $tickets;
    private $email;

    public function __construct($email, $tickets)
    {
        $this->tickets = $tickets;
        $this->email = $email;
    }

    public function totalPrice()
    {
        return $this->tickets->sum('price');
    }

    public function cancel()
    {
        $this->tickets->each(function ($ticket) {
            $ticket->release();
        });
    }

    public function tickets()
    {
        return $this->tickets;
    }

    public function email()
    {
        return $this->email;
    }

    public function complete(PaymentGateway $paymentGateway, $paymentToken)
    {
        $paymentGateway->charge($this->totalPrice(), $paymentToken);
        // TODO 1: get out charge info

        return Order::forTickets($this->email(), $this->tickets(), $this->totalPrice());
        // TODO 2: save last4 to order
    }
}

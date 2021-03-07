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

    public function complete(PaymentGateway $paymentGateway, $paymentToken, $destinationAccountId)
    {
        $charge = $paymentGateway->charge($this->totalPrice(), $paymentToken, $destinationAccountId);

        return Order::forTickets($this->email(), $this->tickets(), $charge);
    }
}

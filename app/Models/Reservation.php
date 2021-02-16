<?php

namespace App\Models;

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
}

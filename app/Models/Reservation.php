<?php

namespace App\Models;

class Reservation
{
    private $tickets;

    public function __construct($tickets)
    {
        $this->tickets = $tickets;
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
}

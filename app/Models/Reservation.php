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
}

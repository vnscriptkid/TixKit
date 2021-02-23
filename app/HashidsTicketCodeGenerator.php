<?php

namespace App;

use Hashids\Hashids;

class HashidsTicketCodeGenerator implements TicketCodeGenerator
{
    private Hashids $hashIds;

    public function __construct(string $salt = 'DefaultSalt')
    {
        $this->hashIds = new Hashids($salt, 6, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
    }

    public function generateFor($ticket)
    {
        return $this->hashIds->encode($ticket->id);
    }
}

<?php

namespace App;

use App\Models\Ticket;

interface TicketCodeGenerator
{
    public function generateFor(Ticket $ticket);
}

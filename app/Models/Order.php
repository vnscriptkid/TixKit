<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function ticketCount()
    {
        return $this->tickets()->count();
    }

    public function cancel()
    {
        // release all tickets associated
        foreach ($this->tickets as $ticket) {
            $ticket->release();
        }

        // delete order itself
        $this->delete();
    }
}

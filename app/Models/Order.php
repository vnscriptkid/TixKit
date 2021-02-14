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

    public function cancel()
    {
        // release all tickets associated
        foreach ($this->tickets as $ticket) {
            $ticket->update(['order_id' => null]);
        }

        // delete order itself
        $this->delete();
    }
}

<?php

namespace App\Models;

use App\Facades\TicketCode;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function scopeAvailable($query)
    {
        return $query->whereNull('order_id')->whereNull('reserved_at');
    }

    public function scopeSold($query)
    {
        return $query->whereNotNull(['order_id']);
    }

    public function release()
    {
        $this->update(['reserved_at' => null]);
    }

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function getPriceAttribute()
    {
        return $this->concert->ticket_price;
    }

    public function reserve()
    {
        $this->update(['reserved_at' => Carbon::now()]);
    }

    public function claimFor($order)
    {
        $this->code = TicketCode::generateFor($this);

        $order->tickets()->save($this);
    }
}

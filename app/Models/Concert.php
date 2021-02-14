<?php

namespace App\Models;

use App\Exceptions\NotEnoughTicketsException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concert extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $dates = ['date', 'published_at'];

    public function orderTickets($email, $ticketQuantity)
    {
        $availableTickets = $this->tickets()->available()->take($ticketQuantity)->get();

        if ($availableTickets->count() < $ticketQuantity)
            throw new NotEnoughTicketsException;

        $order = $this->orders()->create([
            'email' => $email,
            'amount' => $ticketQuantity * $this->ticket_price
        ]);

        foreach ($availableTickets as $ticket) {
            $order->tickets()->save($ticket);
        }

        return $order;
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function getTicketPriceInDollarsAttribute()
    {
        return number_format($this->ticket_price / 100, 2);
    }

    public function getFormattedDateAttribute()
    {
        return $this->date->isoFormat('MMMM DD, YYYY');
    }

    public function getFormattedStartTimeAttribute()
    {
        return $this->date->isoFormat('h:mma');
    }

    public function ticketsRemaining()
    {
        return $this->tickets()->available()->count();
    }

    public function addTickets($numberOfTickets)
    {
        foreach (range(1, $numberOfTickets) as $i) {
            $this->tickets()->create([]);
        }
        return $this;
    }

    public function hasOrderFrom($email)
    {
        return $this->ordersFrom($email)->count() > 0;
    }

    public function ordersFrom($email)
    {
        return $this->orders()->where(['email' => $email]);
    }
}

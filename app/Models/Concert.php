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
        $availableTickets = $this->findTickets($ticketQuantity);

        return $this->createOrder($email, $availableTickets);
    }

    public function createOrder($email, $tickets)
    {
        return Order::forTickets($email, $tickets, $tickets->sum('price'));
    }

    public function findTickets($ticketQuantityNeeded)
    {
        $tickets = $this->tickets()->available()->take($ticketQuantityNeeded)->get();

        if ($tickets->count() < $ticketQuantityNeeded)
            throw new NotEnoughTicketsException;

        return $tickets;
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'tickets');
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

    public function reserveTickets($email, $quantityNeeded)
    {
        $tickets = $this->findTickets($quantityNeeded)->each(function ($ticket) {
            $ticket->reserve();
        });

        return new Reservation($email, $tickets);
    }
}

<?php

namespace App\Models;

use App\Exceptions\NotEnoughTicketsException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concert extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $dates = ['date', 'published_at'];

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

    private function addTickets()
    {
        foreach (range(1, $this->ticket_quantity) as $i) {
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isPublished()
    {
        return $this->published_at !== null;
    }

    public function publish()
    {
        $this->update(['published_at' => $this->freshTimestamp()]);

        $this->addTickets();
    }

    public function ticketsSold()
    {
        return $this->tickets()->sold()->count();
    }

    public function ticketsTotal()
    {
        return $this->tickets()->count();
    }

    public function percentSoldOut()
    {
        return number_format($this->ticketsSold() / $this->ticketsTotal() * 100, 2);
    }
}

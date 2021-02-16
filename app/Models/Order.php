<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function forReservation(Reservation $reservation)
    {
        $order = self::create([
            'email' => $reservation->email(),
            'amount' => $reservation->totalPrice()
        ]);

        foreach ($reservation->tickets() as $ticket) {
            $order->tickets()->save($ticket);
        }

        return $order;
    }

    public static function forTickets($email, $tickets, $amount)
    {
        $order = self::create([
            'email' => $email,
            'amount' => $amount
        ]);

        foreach ($tickets as $ticket) {
            $order->tickets()->save($ticket);
        }

        return $order;
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function concert()
    {
        return $this->belongsToMany(Concert::class, 'tickets');
    }

    public function ticketQuantity()
    {
        return $this->tickets()->count();
    }

    public function cancel()
    {
        foreach ($this->tickets as $ticket) {
            $ticket->release();
        }

        $this->delete();
    }

    public function toArray()
    {
        return [
            'email' => $this->email,
            'ticket_quantity' => $this->ticketQuantity(),
            'amount' => $this->amount
        ];
    }
}

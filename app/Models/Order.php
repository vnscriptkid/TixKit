<?php

namespace App\Models;

use App\Facades\OrderConfirmationNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function forTickets($email, $tickets, $charge)
    {
        $order = self::create([
            'email' => $email,
            'amount' => $charge->amount(),
            'card_last_four' => $charge->cardLastFour(),
            'confirmation_number' => OrderConfirmationNumber::generate()
        ]);

        foreach ($tickets as $ticket) {
            $order->tickets()->save($ticket);
        }

        $tickets->each->claimFor($order);

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
            'amount' => $this->amount,
            'confirmation_number' => $this->confirmation_number,
            'tickets' => $this->tickets->map(function ($ticket) {
                return ['code' => $ticket->code];
            })->all()
        ];
    }

    public static function findByConfirmationNumber($confirmationNumber)
    {
        return self::where('confirmation_number', $confirmationNumber)->firstOrFail();
    }
}

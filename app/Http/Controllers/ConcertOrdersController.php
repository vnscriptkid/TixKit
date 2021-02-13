<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Billing\PaymentGateway;
use App\Models\Concert;

class ConcertOrdersController extends Controller
{
    private $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function store($concertId)
    {
        $concert = Concert::findOrFail($concertId);

        $token = request('payment_token');
        $ticketQuantity = request('ticket_quantity');
        $email = request('email');

        $amount = $ticketQuantity * $concert->ticket_price;

        $this->paymentGateway->charge($amount, $token);

        $order = $concert->orders()->create([
            'email' => $email
        ]);

        foreach (range(1, $ticketQuantity) as $i) {
            $order->tickets()->create();
        }

        return response()->json([], 201);
    }
}

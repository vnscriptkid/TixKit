<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Billing\PaymentGateway;
use App\Models\Concert;
use App\Billing\PaymentFailedException;

class ConcertOrdersController extends Controller
{
    private $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function store($concertId)
    {
        request()->validate([
            'email' => 'required|email',
            'ticket_quantity' => 'required|gte:1',
            'payment_token' => 'required'
        ]);

        $concert = Concert::findOrFail($concertId);

        $amount = request('ticket_quantity') * $concert->ticket_price;

        try {

            $this->paymentGateway->charge($amount, request('payment_token'));

            $concert->orderTickets(request('email'), request('ticket_quantity'));
        } catch (PaymentFailedException $e) {
            return response()->json([], 422);
        }


        return response()->json([], 201);
    }
}

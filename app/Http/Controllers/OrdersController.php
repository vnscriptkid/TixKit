<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function show($confirmation_number)
    {
        $order = Order::where('confirmation_number', $confirmation_number)->first();

        if ($order === null) {
            return response()->json([], 404);
        }

        return view('orders.show', compact('order'));
    }
}

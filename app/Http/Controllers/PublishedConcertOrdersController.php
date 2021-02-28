<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublishedConcertOrdersController extends Controller
{
    public function index($id)
    {
        $concert = Auth::user()->concerts()->published()->findOrFail($id);

        $orders = $concert->orders()->latest()->take(10)->get();

        return view('backstage.published-concert-orders.index', [
            'concert' => $concert,
            'orders' => $orders
        ]);
    }
}

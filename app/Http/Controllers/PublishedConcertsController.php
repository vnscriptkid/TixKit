<?php

namespace App\Http\Controllers;

use App\Models\Concert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublishedConcertsController extends Controller
{
    public function store()
    {
        request()->validate([
            'id' => ['required']
        ]);

        $concert = Auth::user()->concerts()->findOrFail(request('id'));

        abort_if($concert->isPublished(), 302);

        $concert->publish();

        return redirect('/backstage/concerts');
    }
}

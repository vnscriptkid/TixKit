<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login()
    {
        $credentials = request()->only('email', 'password');

        if (Auth::attempt($credentials)) {
            return redirect('/backstage/concerts/new');
        }

        return redirect('/login')
            ->withInput(['email' => request('email')])
            ->withErrors([
                'email' => 'Invalid credentials.'
            ]);
    }

    public function logout()
    {
        Auth::logout();

        return redirect('/login');
    }
}

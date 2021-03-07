<?php

use App\Http\Controllers\Backstage\ConcertMessagesController;
use App\Http\Controllers\Backstage\ConcertsController as BackstageConcertsController;
use App\Http\Controllers\ConcertOrdersController;
use App\Http\Controllers\ConcertsController;
use App\Http\Controllers\InvitationsController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\PublishedConcertOrdersController;
use App\Http\Controllers\PublishedConcertsController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\StripeConnectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return 'Laravel';
});

Route::get('/concerts/{id}', [ConcertsController::class, 'show'])->name('concerts.show');
Route::post('/concerts/{id}/orders', [ConcertOrdersController::class, 'store']);
Route::get('/orders/{confirmation_number}', [OrdersController::class, 'show']);

Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/login', [LoginController::class, 'showLoginForm']);
Route::post('/register', [RegisterController::class, 'register']);

Route::get('/invitations/{code}', [InvitationsController::class, 'show']);

Route::middleware(['auth'])->prefix('backstage')->group(function () {
    Route::get('/concerts/new', [BackstageConcertsController::class, 'create'])->name('backstage.concerts.new');
    Route::get('/concerts/{id}/edit', [BackstageConcertsController::class, 'edit'])->name('backstage.concerts.edit');
    Route::patch('/concerts/{id}', [BackstageConcertsController::class, 'update'])->name('backstage.concerts.update');
    Route::post('/concerts', [BackstageConcertsController::class, 'store']);
    Route::get('/concerts', [BackstageConcertsController::class, 'index'])->name('backstage.concerts.index');
    Route::post('/published-concerts', [PublishedConcertsController::class, 'store'])->name('backstage.published-concerts.store');
    Route::get('/published-concerts/{id}/orders', [PublishedConcertOrdersController::class, 'index'])
        ->name('backstage.published-concert-orders.index');
    Route::get('/concerts/{id}/messages/new', [ConcertMessagesController::class, 'create'])->name('backstage.concert-messages.new');
    Route::post('/concerts/{id}/messages', [ConcertMessagesController::class, 'store'])->name('backstage.concert-messages.store');
    Route::get('/stripe-connect/connect', [StripeConnectController::class, 'connect'])->name('backstage.stripe-connect.connect');
    Route::get('/stripe-connect/authorize', [StripeConnectController::class, 'authorizeRedirect'])->name('backstage.stripe-connect.authorize');
    Route::get('/stripe-connect/redirect', [StripeConnectController::class, 'redirect'])->name('backstage.stripe-connect.redirect');
});

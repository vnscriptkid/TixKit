<?php

use App\Http\Controllers\Backstage\ConcertsController as BackstageConcertsController;
use App\Http\Controllers\ConcertOrdersController;
use App\Http\Controllers\ConcertsController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OrdersController;
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
Route::post('/logout', [LoginController::class, 'logout']);
Route::get('/login', [LoginController::class, 'showLoginForm']);

Route::middleware(['auth'])->prefix('backstage')->group(function () {
    Route::get('/concerts/new', [BackstageConcertsController::class, 'create'])->name('backstage.concerts.new');
    Route::get('/concerts/{id}/edit', [BackstageConcertsController::class, 'edit'])->name('backstage.concerts.edit');
    Route::post('/concerts', [BackstageConcertsController::class, 'store']);
    Route::get('/concerts', [BackstageConcertsController::class, 'index']);
});

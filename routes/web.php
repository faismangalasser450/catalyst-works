<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::redirect('/', '/dashboard');
    Route::view('/dashboard', 'placeholder', ['title' => 'Dashboard'])->name('dashboard');
    Route::view('/my', 'placeholder', ['title' => 'My jobs'])->name('my');
    Route::view('/search', 'placeholder', ['title' => 'Search'])->name('search');
    foreach (['customers', 'enquiries', 'quotations', 'orders', 'invoices', 'complaints', 'contracts', 'ppm', 'settings'] as $m) {
        Route::view("/$m", 'placeholder', ['title' => ucfirst($m)])->name("$m.index");
        Route::view("/$m/new", 'placeholder', ['title' => 'New '.$m])->name("$m.create");
    }
});

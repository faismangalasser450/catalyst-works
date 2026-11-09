<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::redirect('/', '/dashboard');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::view('/my', 'placeholder', ['title' => 'My jobs'])->name('my');
    Route::view('/search', 'placeholder', ['title' => 'Search'])->name('search');
    Route::get('/complaints', \App\Livewire\Complaints\Index::class)->name('complaints.index');
    Route::get('/complaints/new', [ComplaintController::class, 'create'])->name('complaints.create');
    Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
    Route::get('/complaints/{complaint}', \App\Livewire\Complaints\Show::class)->name('complaints.show');
    Route::get('/complaints/{complaint}/edit', [ComplaintController::class, 'edit'])->name('complaints.edit');
    Route::put('/complaints/{complaint}', [ComplaintController::class, 'update'])->name('complaints.update');
    Route::post('/complaints/{complaint}/assign', [ComplaintController::class, 'assign'])->name('complaints.assign');
    Route::post('/complaints/{complaint}/status', [ComplaintController::class, 'status'])->name('complaints.status');

    foreach (['customers', 'enquiries', 'quotations', 'orders', 'invoices', 'contracts', 'ppm', 'settings'] as $m) {
        Route::view("/$m", 'placeholder', ['title' => ucfirst($m)])->name("$m.index");
        Route::view("/$m/new", 'placeholder', ['title' => 'New '.$m])->name("$m.create");
        Route::get("/$m/{id}", fn () => view('placeholder', ['title' => ucfirst($m)]))->name("$m.show");
    }
});

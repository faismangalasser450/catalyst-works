<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\QuotationController;
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
    Route::get('/customers', \App\Livewire\Customers\Index::class)->name('customers.index');
    Route::get('/customers/new', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');

    Route::get('/enquiries', \App\Livewire\Enquiries\Index::class)->name('enquiries.index');
    Route::get('/enquiries/new', [EnquiryController::class, 'create'])->name('enquiries.create');
    Route::post('/enquiries', [EnquiryController::class, 'store'])->name('enquiries.store');
    Route::get('/enquiries/{enquiry}', [EnquiryController::class, 'show'])->name('enquiries.show');
    Route::get('/enquiries/{enquiry}/edit', [EnquiryController::class, 'edit'])->name('enquiries.edit');
    Route::put('/enquiries/{enquiry}', [EnquiryController::class, 'update'])->name('enquiries.update');

    Route::get('/quotations', \App\Livewire\Quotations\Index::class)->name('quotations.index');
    Route::get('/quotations/new', \App\Livewire\Quotations\Editor::class)->name('quotations.create');
    Route::get('/quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');
    Route::get('/quotations/{quotation}/edit', \App\Livewire\Quotations\Editor::class)->name('quotations.edit');
    Route::get('/quotations/{quotation}/pdf', [QuotationController::class, 'pdf'])->name('quotations.pdf');
    Route::post('/quotations/{quotation}/sent', [QuotationController::class, 'sent'])->name('quotations.sent');
    Route::post('/quotations/{quotation}/approve', [QuotationController::class, 'approve'])->name('quotations.approve');
    Route::post('/quotations/{quotation}/reject', [QuotationController::class, 'reject'])->name('quotations.reject');
    Route::get('/quotations/{quotation}/revise', [QuotationController::class, 'revise'])->name('quotations.revise');
    Route::get('/quotations/{quotation}/order', [QuotationController::class, 'order'])->name('quotations.order');
    Route::get('/quotations/{quotation}/invoice', [QuotationController::class, 'invoice'])->name('quotations.invoice');

    Route::get('/complaints', \App\Livewire\Complaints\Index::class)->name('complaints.index');
    Route::get('/complaints/new', [ComplaintController::class, 'create'])->name('complaints.create');
    Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
    Route::get('/complaints/{complaint}', \App\Livewire\Complaints\Show::class)->name('complaints.show');
    Route::get('/complaints/{complaint}/edit', [ComplaintController::class, 'edit'])->name('complaints.edit');
    Route::put('/complaints/{complaint}', [ComplaintController::class, 'update'])->name('complaints.update');
    Route::post('/complaints/{complaint}/assign', [ComplaintController::class, 'assign'])->name('complaints.assign');
    Route::post('/complaints/{complaint}/status', [ComplaintController::class, 'status'])->name('complaints.status');

    foreach (['orders', 'invoices', 'contracts', 'ppm', 'settings'] as $m) {
        Route::view("/$m", 'placeholder', ['title' => ucfirst($m)])->name("$m.index");
        Route::view("/$m/new", 'placeholder', ['title' => 'New '.$m])->name("$m.create");
        Route::get("/$m/{id}", fn () => view('placeholder', ['title' => ucfirst($m)]))->name("$m.show");
    }
});

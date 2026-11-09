<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\PpmController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OrderController;
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
    Route::get('/my', \App\Livewire\My\Jobs::class)->name('my');
    Route::get('/search', SearchController::class)->name('search');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/users', [SettingsController::class, 'storeUser'])->name('settings.users.store');
    Route::put('/settings/users/{user}', [SettingsController::class, 'updateUser'])->name('settings.users.update');
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

    Route::get('/orders', \App\Livewire\Orders\Index::class)->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::post('/orders/{order}/status', [OrderController::class, 'status'])->name('orders.status');

    Route::get('/invoices', \App\Livewire\Invoices\Index::class)->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::post('/invoices/{invoice}/pay', [InvoiceController::class, 'pay'])->name('invoices.pay');
    Route::post('/invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');

    Route::get('/contracts', \App\Livewire\Contracts\Index::class)->name('contracts.index');
    Route::get('/contracts/{contract}', [ContractController::class, 'show'])->name('contracts.show');
    Route::put('/contracts/{contract}', [ContractController::class, 'update'])->name('contracts.update');
    Route::get('/contracts/{contract}/renew', [ContractController::class, 'renew'])->name('contracts.renew');
    Route::post('/contracts/{contract}/cancel', [ContractController::class, 'cancel'])->name('contracts.cancel');
    Route::get('/ppm', \App\Livewire\Ppm\Index::class)->name('ppm.index');
    Route::post('/ppm/{visit}/done', [PpmController::class, 'done'])->name('ppm.done');

    Route::get('/complaints', \App\Livewire\Complaints\Index::class)->name('complaints.index');
    Route::get('/complaints/new', [ComplaintController::class, 'create'])->name('complaints.create');
    Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
    Route::get('/complaints/{complaint}', \App\Livewire\Complaints\Show::class)->name('complaints.show');
    Route::get('/complaints/{complaint}/edit', [ComplaintController::class, 'edit'])->name('complaints.edit');
    Route::put('/complaints/{complaint}', [ComplaintController::class, 'update'])->name('complaints.update');
    Route::post('/complaints/{complaint}/assign', [ComplaintController::class, 'assign'])->name('complaints.assign');
    Route::post('/complaints/{complaint}/status', [ComplaintController::class, 'status'])->name('complaints.status');

});

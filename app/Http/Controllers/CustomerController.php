<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function show(Customer $customer)
    {
        $customer->load(['enquiries.user', 'quotations', 'orders', 'invoices', 'complaints.technician', 'contracts']);

        return view('customers.show', compact('customer'));
    }

    public function create()
    {
        return view('customers.form', ['customer' => new Customer(['emirate' => 'Sharjah'])]);
    }

    public function store(Request $request)
    {
        $customer = Customer::create($this->validated($request) + ['code' => Customer::nextCode('C')]);

        return match ($request->input('then')) {
            'complaint' => redirect()->route('complaints.create', ['customer' => $customer->id])->with('ok', "$customer->name added. Now the complaint:"),
            'enquiry' => redirect()->route('enquiries.create', ['customer' => $customer->id])->with('ok', "$customer->name added. Now the enquiry:"),
            default => redirect()->route('customers.show', $customer)->with('ok', "$customer->name added."),
        };
    }

    public function edit(Customer $customer)
    {
        return view('customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validated($request));

        return redirect()->route('customers.show', $customer)->with('ok', 'Saved.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:120', 'phone' => 'nullable|string|max:30', 'email' => 'nullable|email|max:120',
            'trn' => 'nullable|string|max:20', 'address' => 'nullable|string|max:200', 'city' => 'nullable|string|max:80',
            'emirate' => 'nullable|string|max:40', 'contact_name' => 'nullable|string|max:80', 'contact_phone' => 'nullable|string|max:30',
            'notes' => 'nullable|string|max:1000',
        ]);
    }
}

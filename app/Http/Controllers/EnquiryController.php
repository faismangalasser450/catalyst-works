<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    public function show(Enquiry $enquiry)
    {
        $enquiry->load(['customer', 'user', 'quotations', 'activities.user']);

        return view('enquiries.show', compact('enquiry'));
    }

    public function create(Request $request)
    {
        return view('enquiries.form', [
            'enquiry' => new Enquiry(['customer_id' => $request->integer('customer'), 'date' => today(), 'user_id' => $request->user()->id, 'source' => 'Phone', 'kind' => 'sales']),
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'city']),
            'owners' => User::whereIn('role', ['sales', 'admin'])->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $enquiry = Enquiry::create($data + ['code' => Enquiry::nextCode('ENQ-'), 'kind' => $data['type'] === 'AMC' ? 'amc' : 'sales']);
        $enquiry->log('created');

        if ($request->input('then') === 'quote') {
            return redirect()->route('quotations.create', ['enquiry' => $enquiry->id])->with('ok', "$enquiry->code saved. Now the quotation:");
        }

        return redirect()->route('enquiries.show', $enquiry)->with('ok', "$enquiry->code saved.");
    }

    public function edit(Enquiry $enquiry)
    {
        return view('enquiries.form', [
            'enquiry' => $enquiry,
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'city']),
            'owners' => User::whereIn('role', ['sales', 'admin'])->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Enquiry $enquiry)
    {
        $data = $this->validated($request);
        $enquiry->update($data + ['kind' => $data['type'] === 'AMC' ? 'amc' : 'sales']);

        return redirect()->route('enquiries.show', $enquiry)->with('ok', 'Saved.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'customer_id' => 'required|exists:customers,id', 'user_id' => 'required|exists:users,id', 'date' => 'required|date',
            'type' => 'required|string|max:60', 'source' => 'nullable|string|max:40', 'description' => 'required|string|max:1000',
            'client_ref' => 'nullable|string|max:60', 'follow_up_on' => 'nullable|date',
        ]);
    }
}

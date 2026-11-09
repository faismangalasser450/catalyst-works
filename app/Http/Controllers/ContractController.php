<?php

namespace App\Http\Controllers;

use App\Models\AmcContract;
use App\Models\Quotation;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function show(AmcContract $contract)
    {
        $contract->load(['customer', 'quotation', 'visits.technician', 'complaints.technician', 'invoices', 'activities.user']);

        return view('contracts.show', ['c' => $contract, 'technicians' => User::where('role', 'technician')->where('active', true)->orderBy('name')->get()]);
    }

    public function update(Request $request, AmcContract $contract)
    {
        $data = $request->validate(['site_name' => 'required|string|max:160', 'equipment' => 'required|string|max:300', 'start_date' => 'required|date', 'end_date' => 'required|date|after:start_date', 'visits_per_year' => 'required|integer|min:1|max:12', 'value' => 'required|numeric|min:0', 'notes' => 'nullable|string|max:1000']);
        $reschedule = $contract->visits()->where('status', 'done')->doesntExist() && ($data['visits_per_year'] != $contract->visits_per_year || $data['start_date'] != $contract->start_date->format('Y-m-d'));
        $contract->update($data);
        if ($reschedule) {
            $contract->schedulevisits();
        }
        $contract->log('edited');

        return back()->with('ok', 'Saved.'.($reschedule ? ' PPM visits re-planned.' : ''));
    }

    public function renew(AmcContract $contract)
    {
        $q = Quotation::create([
            'code' => Quotation::nextCode('amc'), 'kind' => 'amc', 'customer_id' => $contract->customer_id, 'user_id' => auth()->id(), 'date' => today(),
            'valid_days' => (int) Setting::get('quote_valid_days', 15), 'vat_pct' => (float) Setting::get('vat_pct', 5), 'discount_pct' => 0,
            'subject' => 'Renewal of AMC '.$contract->code.' — '.$contract->site_name, 'status' => 'draft',
            'payment_terms' => Setting::get('payment_terms'), 'delivery_terms' => Setting::get('delivery_terms'), 'warranty_terms' => Setting::get('warranty_terms'), 'availability' => Setting::get('availability'),
        ]);
        $q->items()->create(['description' => $contract->equipment.' — annual maintenance, '.$contract->visits_per_year.' PPM visits + breakdown calls, '.$contract->end_date->copy()->addDay()->format('d-m-Y').' to '.$contract->end_date->copy()->addYear()->format('d-m-Y'), 'qty' => 1, 'unit_price' => round($contract->value / (1 + $contract->quotation?->vat_pct / 100 ?: 1.05), 2), 'total' => 0, 'sort' => 0]);
        $q->items()->update(['total' => \DB::raw('qty * unit_price')]);
        $q->recalculate();
        $q->log('created', 'Renewal of '.$contract->code);
        $contract->log('renewal', 'Quotation '.$q->ref.' prepared');

        return redirect()->route('quotations.edit', $q)->with('ok', 'Renewal quotation drafted from the old contract. Check the price and send.');
    }

    public function cancel(AmcContract $contract)
    {
        $contract->update(['status' => 'cancelled']);
        $contract->log('status', 'Cancelled');

        return back()->with('ok', $contract->code.' cancelled.');
    }
}

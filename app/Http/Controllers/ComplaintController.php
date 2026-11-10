<?php

namespace App\Http\Controllers;

use App\Models\AmcContract;
use App\Models\Complaint;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function create(Request $request)
    {
        return view('complaints.create', [
            'complaint' => new Complaint(['priority' => 'normal', 'customer_id' => $request->integer('customer')]),
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'city']),
            'technicians' => User::where('role', 'technician')->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $contract = AmcContract::where('customer_id', $data['customer_id'])->where('status', 'active')->latest('end_date')->first();
        $complaint = Complaint::create($data + [
            'code' => Complaint::nextCode('CMP-'),
            'amc_contract_id' => $contract?->id,
            'chargeable' => ! $contract,
            'status' => ! empty($data['technician_id']) ? 'assigned' : 'open',
            'reported_at' => now(),
            'created_by' => $request->user()->id,
        ]);
        $complaint->log('created');
        if ($complaint->technician_id) {
            $complaint->log('assigned', $complaint->technician->name);
        }

        if ($request->input('then') === 'another') {
            return redirect()->route('complaints.create')->with('ok', "$complaint->code saved. Next one:");
        }

        return redirect()->route('complaints.show', $complaint)->with('ok', "$complaint->code registered.");
    }

    public function edit(Complaint $complaint)
    {
        return view('complaints.create', [
            'complaint' => $complaint,
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'city']),
            'technicians' => User::where('role', 'technician')->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Complaint $complaint)
    {
        $complaint->update($this->validated($request));
        $complaint->log('edited');

        return redirect()->route('complaints.show', $complaint)->with('ok', 'Saved.');
    }

    public function assign(Request $request, Complaint $complaint)
    {
        $techId = $request->input('technician_id');
        $complaint->update(['technician_id' => $techId ?: null, 'status' => $techId && $complaint->status === 'open' ? 'assigned' : $complaint->status]);
        $complaint->log('assigned', $techId ? User::find($techId)->name : 'Unassigned');

        return back()->with('ok', "$complaint->code updated.");
    }

    public function status(Request $request, Complaint $complaint)
    {
        $status = $request->validate(['status' => 'required|in:'.implode(',', array_keys(Complaint::STATUSES))])['status'];
        if ($status === 'closed') {
            return redirect()->route('complaints.show', [$complaint, 'close' => 1]);
        }
        $complaint->setStatus($status);

        return back()->with('ok', "$complaint->code is now ".Complaint::STATUSES[$status].'.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'location' => 'required|string|max:120',
            'type' => 'required|string|max:60',
            'equipment' => 'nullable|string|max:60',
            'description' => 'required|string|max:1000',
            'priority' => 'required|in:low,normal,high',
            'technician_id' => 'nullable|exists:users,id',
            'contact_name' => 'nullable|string|max:80',
            'contact_phone' => 'nullable|string|max:30',
        ]);
    }
}

<?php

namespace App\Livewire\Complaints;

use App\Models\Complaint;
use App\Models\User;
use Livewire\Component;

class Show extends Component
{
    public Complaint $complaint;

    public string $panel = '';
    public string $visited_at = '';
    public string $hours = '1';
    public string $notes = '';
    public string $material = '';
    public string $qty = '1';
    public string $unit_cost = '';
    public string $closing_note = '';

    public function mount(): void
    {
        $this->visited_at = now()->format('Y-m-d\TH:i');
    }

    public function open(string $panel): void
    {
        $this->panel = $this->panel === $panel ? '' : $panel;
        $this->resetErrorBag();
    }

    public function assign(string $techId): void
    {
        \App\Http\Middleware\EnsureRole::write('complaints');
        $this->complaint->update(['technician_id' => $techId ?: null, 'status' => $techId && $this->complaint->status === 'open' ? 'assigned' : $this->complaint->status]);
        $this->complaint->log('assigned', $techId ? User::find($techId)->name : 'Unassigned');
    }

    public function setStatus(string $status): void
    {
        \App\Http\Middleware\EnsureRole::write('complaints');
        abort_unless(array_key_exists($status, Complaint::STATUSES), 422);
        if ($status === 'closed') {
            $this->open('close');

            return;
        }
        $this->complaint->setStatus($status);
    }

    public function logVisit(): void
    {
        \App\Http\Middleware\EnsureRole::write('complaints');
        $data = $this->validate(['visited_at' => 'required|date', 'hours' => 'required|numeric|min:0.25|max:24', 'notes' => 'required|string|max:1000']);
        $this->complaint->visits()->create($data + ['technician_id' => $this->complaint->technician_id ?? auth()->id()]);
        if (in_array($this->complaint->status, ['open', 'assigned'])) {
            $this->complaint->update(['status' => 'visited']);
        }
        $this->complaint->log('visit', $data['notes']);
        $this->reset('panel', 'notes');
        $this->hours = '1';
    }

    public function addMaterial(): void
    {
        \App\Http\Middleware\EnsureRole::write('complaints');
        $data = $this->validate(['material' => 'required|string|max:200', 'qty' => 'required|numeric|min:0.01', 'unit_cost' => 'required|numeric|min:0']);
        $this->complaint->materials()->create(['description' => $data['material'], 'qty' => $data['qty'], 'unit_cost' => $data['unit_cost']]);
        $this->complaint->log('material', $data['material'].' × '.$data['qty']);
        $this->reset('material', 'unit_cost');
        $this->qty = '1';
    }

    public function removeMaterial(int $id): void
    {
        \App\Http\Middleware\EnsureRole::write('complaints');
        $this->complaint->materials()->whereKey($id)->delete();
    }

    public function close(): void
    {
        \App\Http\Middleware\EnsureRole::write('complaints');
        $this->validate(['closing_note' => 'required|string|max:1000']);
        $this->complaint->setStatus('closed', $this->closing_note);
        $this->reset('panel');
    }

    public function reopen(): void
    {
        \App\Http\Middleware\EnsureRole::write('complaints');
        $this->complaint->update(['status' => $this->complaint->technician_id ? 'assigned' : 'open', 'closed_at' => null]);
        $this->complaint->log('status', 'Reopened');
    }

    public function render()
    {
        $this->complaint->load(['customer', 'technician', 'contract', 'visits.technician', 'materials', 'activities.user']);

        return view('livewire.complaints.show', [
            'technicians' => User::where('role', 'technician')->where('active', true)->orderBy('name')->get(),
        ])->layout('components.layouts.app', ['title' => $this->complaint->code]);
    }
}

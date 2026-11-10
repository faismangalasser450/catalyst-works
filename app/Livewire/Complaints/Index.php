<?php

namespace App\Livewire\Complaints;

use App\Http\Middleware\EnsureRole;
use App\Models\Complaint;
use App\Models\User;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $q = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $tech = '';

    #[Url]
    public string $period = '';

    public ?int $closing = null;

    public string $note = '';

    public function updated($name): void
    {
        $this->resetPage();
    }

    public function assign(int $id, string $techId): void
    {
        EnsureRole::write('complaints');
        $c = Complaint::findOrFail($id);
        $c->update(['technician_id' => $techId ?: null, 'status' => $techId && $c->status === 'open' ? 'assigned' : $c->status]);
        $c->log('assigned', $techId ? User::find($techId)->name : 'Unassigned');
    }

    public function setStatus(int $id, string $status): void
    {
        EnsureRole::write('complaints');
        abort_unless(array_key_exists($status, Complaint::STATUSES), 422);
        $complaint = Complaint::findOrFail($id);
        if ($status === 'closed') {
            $this->closing = $complaint->id;
            $this->note = '';
            $this->resetErrorBag();

            return;
        }
        $complaint->setStatus($status);
    }

    public function close(): void
    {
        EnsureRole::write('complaints');
        $this->validate(['note' => 'required|string|max:1000'], ['note.required' => 'Write what was done before closing.']);
        Complaint::findOrFail($this->closing)->setStatus('closed', $this->note);
        $this->reset('closing', 'note');
    }

    public function render()
    {
        $base = Complaint::query()
            ->when($this->q, fn ($q) => $q->where(fn ($w) => $w->whereLike('code', "%{$this->q}%")->orWhereLike('location', "%{$this->q}%")->orWhereLike('description', "%{$this->q}%")->orWhereHas('customer', fn ($c) => $c->whereLike('name', "%{$this->q}%"))))
            ->when($this->tech === 'none', fn ($q) => $q->whereNull('technician_id'))
            ->when($this->tech && $this->tech !== 'none', fn ($q) => $q->where('technician_id', $this->tech))
            ->when($this->period === 'today', fn ($q) => $q->where('reported_at', '>=', today()))
            ->when($this->period === 'week', fn ($q) => $q->where('reported_at', '>=', now()->subDays(7)))
            ->when($this->period === 'month', fn ($q) => $q->where('reported_at', '>=', now()->startOfMonth()));

        $counts = (clone $base)->selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');
        $complaints = (clone $base)
            ->when($this->status === '', fn ($q) => $q->where('status', '!=', 'closed'))
            ->when($this->status && $this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->with(['customer', 'technician'])
            ->orderByRaw("case when status = 'closed' then 1 else 0 end")->orderByRaw("case priority when 'high' then 0 else 1 end")->orderBy('reported_at')
            ->paginate(25);

        return view('livewire.complaints.index', [
            'complaints' => $complaints,
            'counts' => $counts,
            'closingComplaint' => $this->closing ? Complaint::with('customer')->find($this->closing) : null,
            'technicians' => User::where('role', 'technician')->where('active', true)->orderBy('name')->get(),
        ])->layout('components.layouts.app', ['title' => 'Complaints']);
    }
}

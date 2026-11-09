<?php

namespace App\Livewire\Quotations;

use App\Models\Quotation;
use App\Services\QuotationActions;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url] public string $q = '';
    #[Url] public string $status = '';
    #[Url] public string $kind = '';
    public ?int $rejecting = null;
    public string $reason = '';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function markSent(int $id): void
    {
        \App\Http\Middleware\EnsureRole::write('quotations');
        app(QuotationActions::class)->markSent(Quotation::findOrFail($id));
    }

    public function approve(int $id): void
    {
        \App\Http\Middleware\EnsureRole::write('quotations');
        app(QuotationActions::class)->approve(Quotation::findOrFail($id));
    }

    public function reject(): void
    {
        \App\Http\Middleware\EnsureRole::write('quotations');
        $this->validate(['reason' => 'required|string|max:200']);
        app(QuotationActions::class)->reject(Quotation::findOrFail($this->rejecting), $this->reason);
        $this->reset('rejecting', 'reason');
    }

    public function render()
    {
        $base = Quotation::query()
            ->when($this->q, fn ($q) => $q->where(fn ($w) => $w->where('code', 'like', "%{$this->q}%")->orWhere('subject', 'like', "%{$this->q}%")->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$this->q}%"))))
            ->when($this->kind, fn ($q) => $q->where('kind', $this->kind));
        $counts = (clone $base)->selectRaw('status, count(*) c, sum(total) v')->groupBy('status')->get()->keyBy('status');
        $quotations = (clone $base)
            ->when($this->status === '', fn ($q) => $q->whereIn('status', ['draft', 'sent', 'approved']))
            ->when($this->status && $this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->with(['customer', 'user'])->latest('date')->latest('id')->paginate(25);

        return view('livewire.quotations.index', compact('quotations', 'counts'))->layout('components.layouts.app', ['title' => 'Quotations']);
    }
}

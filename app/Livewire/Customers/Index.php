<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $q = '';

    #[Url]
    public string $emirate = '';

    #[Url]
    public string $has = '';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $customers = Customer::query()
            ->withCount(['complaints as open_complaints' => fn ($q) => $q->where('status', '!=', 'closed'), 'contracts as active_contracts' => fn ($q) => $q->where('status', 'active'), 'quotations as open_quotes' => fn ($q) => $q->whereIn('status', ['draft', 'sent'])])
            ->withSum(['invoices as due' => fn ($q) => $q->whereIn('status', ['unpaid', 'part_paid'])], 'total')
            ->withSum(['invoices as paid' => fn ($q) => $q->whereIn('status', ['unpaid', 'part_paid'])], 'paid_amount')
            ->when($this->q, fn ($q) => $q->where(fn ($w) => $w->whereLike('name', "%{$this->q}%")->orWhereLike('phone', "%{$this->q}%")->orWhereLike('contact_name', "%{$this->q}%")->orWhereLike('code', "%{$this->q}%")))
            ->when($this->emirate, fn ($q) => $q->where('emirate', $this->emirate))
            ->when($this->has === 'amc', fn ($q) => $q->whereHas('contracts', fn ($c) => $c->where('status', 'active')))
            ->when($this->has === 'due', fn ($q) => $q->whereHas('invoices', fn ($c) => $c->whereIn('status', ['unpaid', 'part_paid'])))
            ->orderBy('name')->paginate(25);

        return view('livewire.customers.index', [
            'customers' => $customers,
            'emirates' => Customer::whereNotNull('emirate')->distinct()->orderBy('emirate')->pluck('emirate'),
        ])->layout('components.layouts.app', ['title' => 'Customers']);
    }
}

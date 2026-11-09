<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url] public string $q = '';
    #[Url] public string $status = '';
    public ?int $paying = null;
    public string $amount = '';
    public string $method = 'Bank transfer';
    public string $reference = '';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function startPay(int $id): void
    {
        $inv = Invoice::findOrFail($id);
        $this->paying = $id;
        $this->amount = (string) $inv->balance;
        $this->reference = '';
    }

    public function pay(): void
    {
        $this->validate(['amount' => 'required|numeric|min:0.01', 'method' => 'required|string|max:40', 'reference' => 'nullable|string|max:60']);
        $inv = Invoice::findOrFail($this->paying);
        $inv->payments()->create(['date' => today(), 'amount' => $this->amount, 'method' => $this->method, 'reference' => $this->reference ?: null, 'user_id' => auth()->id()]);
        $inv->refreshPaidStatus();
        $inv->log('payment', money($this->amount, true).' '.$this->method.($this->reference ? ' '.$this->reference : ''));
        $this->reset('paying', 'amount', 'reference');
    }

    public function render()
    {
        $base = Invoice::query()->when($this->q, fn ($q) => $q->where(fn ($w) => $w->where('code', 'like', "%{$this->q}%")->orWhere('lpo_number', 'like', "%{$this->q}%")->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$this->q}%"))));
        $counts = (clone $base)->selectRaw('status, count(*) c, sum(total - paid_amount) due')->groupBy('status')->get()->keyBy('status');
        $invoices = (clone $base)->when($this->status === '' || $this->status === 'unpaid', fn ($q) => $q->whereIn('status', ['unpaid', 'part_paid']))
            ->when($this->status === 'overdue', fn ($q) => $q->whereIn('status', ['unpaid', 'part_paid'])->where('due_date', '<', today()))
            ->when(in_array($this->status, ['paid', 'cancelled', 'part_paid']), fn ($q) => $q->where('status', $this->status))
            ->with(['customer'])->orderBy('due_date')->paginate(25);

        return view('livewire.invoices.index', compact('invoices', 'counts'))->layout('components.layouts.app', ['title' => 'Invoices']);
    }
}

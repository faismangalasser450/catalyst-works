<?php

namespace App\Livewire\Orders;

use App\Models\Order;
use App\Models\User;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url] public string $q = '';
    #[Url] public string $status = '';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function setStatus(int $id, string $status): void
    {
        \App\Http\Middleware\EnsureRole::write('orders');
        abort_unless(array_key_exists($status, Order::STATUSES), 422);
        $o = Order::findOrFail($id);
        $o->update(['status' => $status, 'delivered_on' => $status === 'delivered' ? ($o->delivered_on ?? today()) : $o->delivered_on]);
        $o->log('status', Order::STATUSES[$status]);
    }

    public function assign(int $id, string $techId): void
    {
        \App\Http\Middleware\EnsureRole::write('orders');
        $o = Order::findOrFail($id);
        $o->update(['technician_id' => $techId ?: null]);
        $o->log('assigned', $techId ? User::find($techId)->name : 'Unassigned');
    }

    public function render()
    {
        $base = Order::query()->when($this->q, fn ($q) => $q->where(fn ($w) => $w->where('code', 'like', "%{$this->q}%")->orWhere('po_number', 'like', "%{$this->q}%")->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$this->q}%"))));
        $counts = (clone $base)->selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');
        $orders = (clone $base)->when($this->status === '', fn ($q) => $q->whereIn('status', ['confirmed', 'in_progress']))
            ->when($this->status && $this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->with(['customer', 'quotation', 'technician', 'invoice'])->orderBy('due_date')->paginate(25);

        return view('livewire.orders.index', ['orders' => $orders, 'counts' => $counts, 'technicians' => User::where('role', 'technician')->where('active', true)->orderBy('name')->get()])
            ->layout('components.layouts.app', ['title' => 'Orders']);
    }
}

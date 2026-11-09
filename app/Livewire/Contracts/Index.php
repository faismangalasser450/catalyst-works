<?php

namespace App\Livewire\Contracts;

use App\Models\AmcContract;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url] public string $q = '';
    #[Url] public string $status = 'active';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        AmcContract::where('status', 'active')->where('end_date', '<', today())->update(['status' => 'expired']);
        $base = AmcContract::query()->when($this->q, fn ($q) => $q->where(fn ($w) => $w->where('code', 'like', "%{$this->q}%")->orWhere('site_name', 'like', "%{$this->q}%")->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$this->q}%"))));
        $counts = (clone $base)->selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');
        $due = (clone $base)->where('status', 'active')->where('end_date', '<=', today()->addDays(90))->count();
        $contracts = (clone $base)
            ->when($this->status === 'due', fn ($q) => $q->where('status', 'active')->where('end_date', '<=', today()->addDays(90)))
            ->when($this->status && ! in_array($this->status, ['all', 'due']), fn ($q) => $q->where('status', $this->status))
            ->with('customer')->withCount(['visits as visits_done' => fn ($q) => $q->where('status', 'done'), 'complaints as open_complaints' => fn ($q) => $q->where('status', '!=', 'closed')])
            ->orderBy('end_date')->paginate(25);

        return view('livewire.contracts.index', compact('contracts', 'counts', 'due'))->layout('components.layouts.app', ['title' => 'AMC contracts']);
    }
}

<?php

namespace App\Livewire\Enquiries;

use App\Http\Middleware\EnsureRole;
use App\Models\Enquiry;
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
    public string $status = 'open';

    #[Url]
    public string $owner = '';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function markLost(int $id): void
    {
        EnsureRole::write('enquiries');
        $e = Enquiry::findOrFail($id);
        $e->update(['status' => 'lost']);
        $e->log('status', 'Lost');
    }

    public function render()
    {
        $base = Enquiry::query()
            ->when($this->q, fn ($q) => $q->where(fn ($w) => $w->whereLike('code', "%{$this->q}%")->orWhereLike('description', "%{$this->q}%")->orWhereHas('customer', fn ($c) => $c->whereLike('name', "%{$this->q}%"))))
            ->when($this->owner, fn ($q) => $q->where('user_id', $this->owner));
        $counts = (clone $base)->selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');
        $enquiries = (clone $base)->when($this->status && $this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->with(['customer', 'user'])->withCount('quotations')->latest('date')->latest('id')->paginate(25);

        return view('livewire.enquiries.index', [
            'enquiries' => $enquiries, 'counts' => $counts,
            'owners' => User::whereIn('role', ['sales', 'admin'])->where('active', true)->orderBy('name')->get(),
        ])->layout('components.layouts.app', ['title' => 'Enquiries']);
    }
}

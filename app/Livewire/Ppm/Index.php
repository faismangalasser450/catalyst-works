<?php

namespace App\Livewire\Ppm;

use App\Models\PpmVisit;
use App\Models\User;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url] public string $when = 'month';
    #[Url] public string $tech = '';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function assign(int $id, string $techId): void
    {
        PpmVisit::findOrFail($id)->update(['technician_id' => $techId ?: null]);
    }

    public function done(int $id): void
    {
        $v = PpmVisit::findOrFail($id);
        $v->update(['status' => 'done', 'done_on' => today(), 'technician_id' => $v->technician_id ?? (auth()->user()->isTechnician() ? auth()->id() : null)]);
        $v->contract->log('ppm', 'Visit planned '.dmy($v->scheduled_on).' done');
    }

    public function undo(int $id): void
    {
        PpmVisit::findOrFail($id)->update(['status' => 'due', 'done_on' => null]);
    }

    public function render()
    {
        $visits = PpmVisit::query()->whereHas('contract', fn ($q) => $q->where('status', 'active'))
            ->when($this->when === 'overdue', fn ($q) => $q->where('status', 'due')->where('scheduled_on', '<', today()))
            ->when($this->when === 'week', fn ($q) => $q->where('status', 'due')->where('scheduled_on', '<=', today()->addDays(7)))
            ->when($this->when === 'month', fn ($q) => $q->where(fn ($w) => $w->where('status', 'due')->where('scheduled_on', '<=', today()->endOfMonth())->orWhere('done_on', '>=', today()->startOfMonth())))
            ->when($this->when === 'done', fn ($q) => $q->where('status', 'done'))
            ->when($this->tech, fn ($q) => $q->where('technician_id', $this->tech))
            ->with(['contract.customer', 'technician'])->orderByRaw("case when status = 'due' then 0 else 1 end")->orderBy('scheduled_on')->paginate(40);

        return view('livewire.ppm.index', ['visits' => $visits, 'technicians' => User::where('role', 'technician')->where('active', true)->orderBy('name')->get(),
            'overdue' => PpmVisit::where('status', 'due')->where('scheduled_on', '<', today())->whereHas('contract', fn ($q) => $q->where('status', 'active'))->count()])
            ->layout('components.layouts.app', ['title' => 'PPM visits']);
    }
}

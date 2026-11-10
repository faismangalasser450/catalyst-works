<?php

namespace App\Livewire\My;

use App\Models\Complaint;
use App\Models\PpmVisit;
use Livewire\Component;

class Jobs extends Component
{
    public ?int $closing = null;
    public string $note = '';

    public function status(int $id, string $status): void
    {
        $c = Complaint::where('technician_id', auth()->id())->findOrFail($id);
        if ($status === 'closed') {
            $this->closing = $id;

            return;
        }
        $c->setStatus($status);
    }

    public function close(): void
    {
        $this->validate(['note' => 'required|string|min:3|max:500'], ['note.required' => 'Write one line on what was done.']);
        Complaint::where('technician_id', auth()->id())->findOrFail($this->closing)->setStatus('closed', $this->note);
        $this->reset('closing', 'note');
    }

    public function ppmDone(int $id): void
    {
        $v = PpmVisit::where('technician_id', auth()->id())->findOrFail($id);
        $v->update(['status' => 'done', 'done_on' => today()]);
        $v->contract->log('ppm', 'Visit planned '.dmy($v->scheduled_on).' done');
    }

    public function render()
    {
        $me = auth()->id();

        return view('livewire.my.jobs', [
            'complaints' => Complaint::where('technician_id', $me)->where('status', '!=', 'closed')->with('customer')->orderByRaw("case priority when 'high' then 0 when 'normal' then 1 else 2 end")->orderBy('reported_at')->get(),
            'ppm' => PpmVisit::where('technician_id', $me)->where('status', 'due')->where('scheduled_on', '<=', today()->addDays(7))->with('contract.customer')->orderBy('scheduled_on')->get(),
            'doneToday' => Complaint::where('technician_id', $me)->whereDate('closed_at', today())->count(),
        ])->layout('components.layouts.app', ['title' => 'My jobs']);
    }
}

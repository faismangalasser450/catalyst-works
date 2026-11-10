<div>
    <x-page title="Complaints" sub="Open ones first. Click the coloured status to change it; it saves instantly.">
        <x-slot:actions><a href="{{ route('complaints.create') }}" class="btn btn-primary">+ Complaint</a></x-slot:actions>
    </x-page>

    <div class="card mb-3 card-b flex flex-wrap items-center gap-3">
        <input wire:model.live.debounce.300ms="q" class="input w-full sm:w-64" placeholder="Search no, customer, flat, problem…">
        <select wire:model.live="tech" class="input w-auto"><option value="">All technicians</option><option value="none">Unassigned</option>@foreach($technicians as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select>
        <select wire:model.live="period" class="input w-auto"><option value="">Any date</option><option value="today">Today</option><option value="week">Last 7 days</option><option value="month">This month</option></select>
        <div class="flex flex-wrap gap-1.5">
            @php($open = $counts->except('closed')->sum())
            <button wire:click="$set('status','')" class="chip {{ $status === '' ? 'chip-on' : '' }}">Open {{ $open }}</button>
            @foreach(\App\Models\Complaint::STATUSES as $k => $l)
                <button wire:click="$set('status','{{ $k }}')" class="chip {{ $status === $k ? 'chip-on' : '' }}"><span class="dot mr-1.5 {{ status_dot($k) }}"></span>{{ $l }} {{ $counts[$k] ?? 0 }}</button>
            @endforeach
            <button wire:click="$set('status','all')" class="chip {{ $status === 'all' ? 'chip-on' : '' }}">All</button>
        </div>
    </div>

    @if($closingComplaint)
        <form wire:submit="close" class="card mb-3 card-b flex flex-wrap items-end gap-3 border-green-300">
            <x-field label="Closing {{ $closingComplaint->code }} · {{ $closingComplaint->customer->name }}: what was done?" name="note" required class="flex-1"><input wire:model="note" class="input" placeholder="e.g. Gas topped up, cooling OK, customer signed" autofocus></x-field>
            <button class="btn btn-ok">Close complaint</button><button type="button" wire:click="$set('closing', null)" class="btn">Cancel</button>
        </form>
    @endif

    <div class="card overflow-x-auto">
        <table class="tbl">
            <thead><tr><th>No</th><th>Customer / location</th><th>Problem</th><th>Reported</th><th>Technician</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($complaints as $c)
                <tr wire:key="c{{ $c->id }}">
                    <td><a href="{{ route('complaints.show', $c) }}" class="font-medium text-brand-700">{{ $c->code }}</a>@if($c->priority === 'high')<div><x-badge status="high" label="High" /></div>@endif</td>
                    <td><div class="font-medium">{{ $c->customer->name }}</div><div class="text-xs text-slate-500">{{ $c->location }}@if($c->amc_contract_id) · AMC @endif</div></td>
                    <td><div>{{ $c->type }}</div><div class="max-w-xs truncate text-xs text-slate-500">{{ $c->equipment }} · {{ $c->description }}</div></td>
                    <td class="whitespace-nowrap"><div>{{ dmy($c->reported_at) }}</div><div class="text-xs {{ $c->isOverdue() ? 'font-medium text-red-600' : 'text-slate-500' }}">{{ ago($c->reported_at) }} ago</div></td>
                    <td><select wire:change="assign({{ $c->id }}, $event.target.value)" class="input w-40 py-1 text-xs"><option value="">Unassigned</option>@foreach($technicians as $t)<option value="{{ $t->id }}" @selected($c->technician_id === $t->id)>{{ $t->name }}</option>@endforeach</select></td>
                    <td><x-status-menu :status="$c->status" :options="\App\Models\Complaint::STATUSES" :wire="'setStatus('.$c->id.', \'%s\')'" /></td>
                    <td><a href="{{ route('complaints.show', $c) }}" class="btn btn-sm">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-10 text-center text-slate-500">No complaints match.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $complaints->links() }}</div>
    </div>
</div>

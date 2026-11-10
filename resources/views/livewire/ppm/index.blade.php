<div>
    <x-page title="PPM visits" sub="Planned maintenance visits from active AMC contracts. Tick them off as they are done." />
    <div class="card mb-3 card-b flex flex-wrap items-center gap-3">
        <select wire:model.live="tech" class="input w-auto"><option value="">All technicians</option>@foreach($technicians as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select>
        <div class="flex flex-wrap gap-1.5">
            <button wire:click="$set('when','overdue')" class="chip {{ $when === 'overdue' ? 'chip-on' : '' }} {{ $overdue ? 'border-red-300 text-red-700' : '' }}">Overdue {{ $overdue }}</button>
            <button wire:click="$set('when','week')" class="chip {{ $when === 'week' ? 'chip-on' : '' }}">Next 7 days</button>
            <button wire:click="$set('when','month')" class="chip {{ $when === 'month' ? 'chip-on' : '' }}">This month</button>
            <button wire:click="$set('when','all')" class="chip {{ $when === 'all' ? 'chip-on' : '' }}">All planned</button>
            <button wire:click="$set('when','done')" class="chip {{ $when === 'done' ? 'chip-on' : '' }}">Done</button>
        </div>
    </div>
    <div class="card overflow-x-auto"><table class="tbl">
        <thead><tr><th>Planned</th><th>Customer / site</th><th>Contract</th><th>Equipment</th><th>Technician</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($visits as $v)
            @php($late = $v->status === 'due' && $v->scheduled_on->isPast())
            <tr wire:key="v{{ $v->id }}">
                <td class="whitespace-nowrap {{ $late ? 'font-medium text-red-600' : '' }}">{{ dmy($v->scheduled_on) }}@if($late)<div class="text-xs">{{ ago($v->scheduled_on) }} late</div>@endif</td>
                <td><div class="font-medium">{{ $v->contract->customer->name }}</div><div class="text-xs text-slate-500">{{ $v->contract->site_name }}</div></td>
                <td><a href="{{ route('contracts.show', $v->contract) }}" class="text-brand-700">{{ $v->contract->code }}</a></td>
                <td class="max-w-xs truncate text-slate-600">{{ $v->contract->equipment }}</td>
                <td><select wire:change="assign({{ $v->id }}, $event.target.value)" class="input w-40 py-1 text-xs"><option value="">Unassigned</option>@foreach($technicians as $t)<option value="{{ $t->id }}" @selected($v->technician_id === $t->id)>{{ $t->name }}</option>@endforeach</select></td>
                <td>@if($v->status === 'done')<x-badge status="done" label="Done {{ dmy($v->done_on) }}" />@else<x-badge :status="$late ? 'missed' : 'due'" :label="$late ? 'Overdue' : 'Planned'" />@endif</td>
                <td class="text-right">@if($v->status === 'done')<button wire:click="undo({{ $v->id }})" class="btn btn-sm">Undo</button>@else<button wire:click="done({{ $v->id }})" class="btn btn-sm btn-ok">Done today</button>@endif</td>
            </tr>
        @empty<tr><td colspan="7" class="py-10 text-center text-slate-500">Nothing planned here.</td></tr>@endforelse
        </tbody></table><div class="px-4 py-3">{{ $visits->links() }}</div></div>
</div>

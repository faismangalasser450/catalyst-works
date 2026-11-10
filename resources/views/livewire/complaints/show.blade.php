@php($c = $complaint)
<div>
    <x-page :title="$c->code.' · '.$c->customer->name" :sub="$c->type.' · '.$c->equipment.' · '.$c->location" :back="route('complaints.index')">
        <x-slot:actions>
            @if(\App\Http\Middleware\EnsureRole::allows(auth()->user(), 'complaints', true))
                <x-status-menu :status="$c->status" :options="\App\Models\Complaint::STATUSES" wire="setStatus('%s')" />
            @else
                <x-badge :status="$c->status" />
            @endif
            @if($c->priority === 'high')<x-badge status="high" label="High priority" />@endif
            <a href="{{ route('complaints.edit', $c) }}" class="btn btn-sm">Edit</a>
        </x-slot:actions>
    </x-page>

    {{-- Action bar: one click each --}}
    <div class="card mb-4 card-b flex flex-wrap items-center gap-2">
        @if($c->status === 'closed')
            <span class="text-[13px] text-slate-600">Closed {{ dmy($c->closed_at) }} · {{ $c->closing_note }}</span>
            <button wire:click="reopen" class="btn btn-sm ml-auto">Reopen</button>
        @else
            <select wire:change="assign($event.target.value)" class="input w-48"><option value="">Assign technician…</option>@foreach($technicians as $t)<option value="{{ $t->id }}" @selected($c->technician_id === $t->id)>{{ $t->name }}</option>@endforeach</select>
            <button wire:click="open('visit')" class="btn {{ $panel === 'visit' ? 'btn-primary' : '' }}">Log visit</button>
            <button wire:click="setStatus('in_progress')" class="btn" @disabled($c->status === 'in_progress')>In progress</button>
            <button wire:click="open('material')" class="btn {{ $panel === 'material' ? 'btn-primary' : '' }}">Add material</button>
            <button wire:click="open('close')" class="btn btn-ok ml-auto">Close complaint</button>
        @endif
    </div>

    @if($panel === 'visit')
        <form wire:submit="logVisit" class="card mb-4 card-b grid gap-3 md:grid-cols-4">
            <x-field label="Visited" name="visited_at" required><input type="datetime-local" wire:model="visited_at" class="input"></x-field>
            <x-field label="Hours on site" name="hours" required><input type="number" step="0.25" wire:model="hours" class="input"></x-field>
            <x-field label="What was found / done" name="notes" required class="md:col-span-2"><input wire:model="notes" class="input" placeholder="e.g. Drain line blocked, cleaned and tested" autofocus></x-field>
            <div class="md:col-span-4 flex gap-2"><button class="btn btn-primary">Save visit</button><button type="button" wire:click="open('')" class="btn">Cancel</button></div>
        </form>
    @elseif($panel === 'material')
        <form wire:submit="addMaterial" class="card mb-4 card-b grid gap-3 md:grid-cols-4">
            <x-field label="Material / spare part" name="material" required class="md:col-span-2"><input wire:model="material" class="input" placeholder="e.g. Capacitor 45uF" autofocus></x-field>
            <x-field label="Qty" name="qty" required><input type="number" step="1" wire:model="qty" class="input"></x-field>
            <x-field label="Unit cost (AED)" name="unit_cost" required><input type="number" step="0.01" wire:model="unit_cost" class="input"></x-field>
            <div class="md:col-span-4 flex gap-2"><button class="btn btn-primary">Add</button><button type="button" wire:click="open('')" class="btn">Done</button></div>
        </form>
    @elseif($panel === 'close')
        <form wire:submit="close" class="card mb-4 card-b grid gap-3 md:grid-cols-4">
            <x-field label="Closing note" name="closing_note" required class="md:col-span-3"><input wire:model="closing_note" class="input" placeholder="e.g. Gas topped up, cooling OK, customer signed" autofocus></x-field>
            <div class="flex items-end gap-2"><button class="btn btn-ok">Close complaint</button><button type="button" wire:click="open('')" class="btn">Cancel</button></div>
        </form>
    @endif

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <div class="card">
                <div class="card-h">Complaint</div>
                <div class="card-b grid gap-x-6 gap-y-3 text-[13px] sm:grid-cols-2">
                    <div><div class="text-xs text-slate-500">Reported</div>{{ $c->reported_at->format('d-m-Y H:i') }} · {{ ago($c->reported_at) }} ago</div>
                    <div><div class="text-xs text-slate-500">Registered by</div>{{ $c->creator?->name ?? '—' }}</div>
                    <div class="sm:col-span-2"><div class="text-xs text-slate-500">Description</div>{{ $c->description }}</div>
                    <div><div class="text-xs text-slate-500">Cover</div>{{ $c->contract ? 'AMC '.$c->contract->code.' (until '.dmy($c->contract->end_date).')' : ($c->chargeable ? 'Chargeable' : 'Warranty / free') }}</div>
                    <div><div class="text-xs text-slate-500">Customer contact</div>{{ $c->contact_name ?: $c->customer->contact_name }} · {{ $c->contact_phone ?: $c->customer->phone }}</div>
                </div>
            </div>

            <div class="card">
                <div class="card-h">Visits <span class="text-xs font-normal text-slate-500">{{ $c->visits->count() }}</span></div>
                @forelse($c->visits as $v)
                    <div class="flex gap-3 border-b border-slate-100 px-4 py-3 text-[13px] last:border-0"><div class="w-32 shrink-0 text-slate-500">{{ $v->visited_at->format('d-m-Y H:i') }}</div><div><div>{{ $v->notes }}</div><div class="text-xs text-slate-500">{{ $v->technician?->name }} · {{ $v->hours }} h</div></div></div>
                @empty<div class="px-4 py-6 text-center text-[13px] text-slate-500">No visit yet.</div>@endforelse
            </div>

            <div class="card">
                <div class="card-h">Materials used <span class="text-xs font-normal text-slate-500">{{ money($c->materials->sum(fn ($m) => $m->qty * $m->unit_cost), true) }}</span></div>
                @forelse($c->materials as $m)
                    <div class="flex items-center gap-3 border-b border-slate-100 px-4 py-2 text-[13px] last:border-0"><div class="flex-1">{{ $m->description }}</div><div class="text-slate-500">{{ $m->qty + 0 }} × {{ money($m->unit_cost) }}</div><div class="w-24 text-right font-medium">{{ money($m->qty * $m->unit_cost) }}</div>@if($c->status !== 'closed')<button wire:click="removeMaterial({{ $m->id }})" wire:confirm="Remove this material?" class="text-xs text-slate-400 hover:text-red-600">Remove</button>@endif</div>
                @empty<div class="px-4 py-6 text-center text-[13px] text-slate-500">No materials.</div>@endforelse
            </div>
        </div>

        <div class="card self-start">
            <div class="card-h">History</div>
            <ol class="px-4 py-2 text-[13px]">
                @foreach($c->activities as $a)
                    <li class="flex gap-3 border-l-2 border-slate-200 py-2 pl-3"><div><div>{{ ucfirst($a->action) }}{{ $a->note ? ': '.$a->note : '' }}</div><div class="text-xs text-slate-500">{{ $a->user?->name ?? 'System' }} · {{ $a->created_at->format('d-m-Y H:i') }}</div></div></li>
                @endforeach
            </ol>
        </div>
    </div>
</div>

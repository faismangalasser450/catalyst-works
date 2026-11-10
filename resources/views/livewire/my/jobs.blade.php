<div class="mx-auto max-w-2xl">
    <x-page title="My jobs" sub="{{ $complaints->count() }} open · {{ $ppm->count() }} PPM this week · {{ $doneToday }} closed today" />
    @if($closing)
        @php($cc = $complaints->firstWhere('id', $closing))
        <form wire:submit="close" class="card mb-3 card-b border-brand-100">
            <div class="mb-2 font-medium">Closing {{ $cc?->code }} — what was done?</div>
            <textarea wire:model="note" rows="3" class="input text-base" placeholder="e.g. Cleaned filters, topped up gas, unit cooling OK" autofocus></textarea>
            @error('note')<div class="mt-1 text-sm text-red-600">{{ $message }}</div>@enderror
            <div class="mt-3 flex gap-2"><button class="btn btn-primary btn-lg flex-1 justify-center">Close complaint</button><button type="button" wire:click="$set('closing', null)" class="btn btn-lg">Back</button></div>
        </form>
    @endif
    <div class="space-y-3">
        @forelse($complaints as $c)
            <div class="card card-b" wire:key="c{{ $c->id }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-base font-semibold">{{ $c->customer->name }}</div>
                        <div class="text-sm text-slate-600">{{ $c->location }}</div>
                        <div class="mt-1 text-sm"><span class="font-medium">{{ $c->type }}</span> · {{ $c->equipment }} @if($c->priority === 'high')<x-badge status="high" label="Urgent" />@endif</div>
                        <div class="mt-1 text-sm text-slate-600">{{ $c->description }}</div>
                    </div>
                    <div class="text-right text-xs text-slate-500"><a href="{{ route('complaints.show', $c) }}" class="text-brand-700">{{ $c->code }}</a><div>{{ ago($c->reported_at) }} ago</div><div class="mt-1"><x-badge :status="$c->status" /></div></div>
                </div>
                <div class="mt-3 flex flex-wrap gap-2 text-sm">
                    @if($c->contact_phone ?? $c->customer->phone)<a href="tel:{{ $c->contact_phone ?? $c->customer->phone }}" class="btn btn-lg">Call {{ $c->contact_name ?? $c->customer->contact_name }}</a>@endif
                    <a href="https://maps.google.com/?q={{ urlencode($c->location.', '.$c->customer->city) }}" target="_blank" class="btn btn-lg">Map</a>
                </div>
                <div class="mt-2 grid grid-cols-3 gap-2">
                    <button wire:click="status({{ $c->id }}, 'visited')" class="btn btn-lg justify-center {{ $c->status === 'visited' ? status_solid('visited') : '' }}"><span class="dot {{ $c->status === 'visited' ? 'bg-white' : status_dot('visited') }}"></span>Visited</button>
                    <button wire:click="status({{ $c->id }}, 'in_progress')" class="btn btn-lg justify-center {{ $c->status === 'in_progress' ? status_solid('in_progress') : '' }}"><span class="dot {{ $c->status === 'in_progress' ? 'bg-white' : status_dot('in_progress') }}"></span>Working</button>
                    <button wire:click="status({{ $c->id }}, 'closed')" class="btn btn-lg btn-ok justify-center">Close</button>
                </div>
            </div>
        @empty<div class="card card-b py-10 text-center text-slate-500">No open complaints assigned to you.</div>@endforelse
    </div>
    @if($ppm->count())
        <h2 class="mb-2 mt-6 text-sm font-semibold uppercase tracking-wide text-slate-500">PPM visits this week</h2>
        <div class="space-y-3">
            @foreach($ppm as $v)
                <div class="card card-b flex items-center justify-between gap-3" wire:key="p{{ $v->id }}">
                    <div><div class="font-semibold">{{ $v->contract->customer->name }}</div><div class="text-sm text-slate-600">{{ $v->contract->site_name }} · {{ $v->contract->equipment }}</div><div class="text-xs text-slate-500">Planned {{ dmy($v->scheduled_on) }}</div></div>
                    <button wire:click="ppmDone({{ $v->id }})" class="btn btn-lg btn-primary">Done</button>
                </div>
            @endforeach
        </div>
    @endif
</div>

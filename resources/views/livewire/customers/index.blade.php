<div>
    <x-page title="Customers" sub="{{ $customers->total() }} customers">
        <x-slot:actions><a href="{{ route('customers.create') }}" class="btn btn-primary">+ Customer</a></x-slot:actions>
    </x-page>
    <div class="card mb-3 card-b flex flex-wrap items-center gap-3">
        <input wire:model.live.debounce.300ms="q" class="input w-full sm:w-72" placeholder="Search name, phone, contact…" autofocus>
        <select wire:model.live="emirate" class="input w-auto"><option value="">All emirates</option>@foreach($emirates as $e)<option>{{ $e }}</option>@endforeach</select>
        <div class="flex gap-1.5">
            <button wire:click="$set('has','')" class="chip {{ $has === '' ? 'chip-on' : '' }}">All</button>
            <button wire:click="$set('has','amc')" class="chip {{ $has === 'amc' ? 'chip-on' : '' }}">With active AMC</button>
            <button wire:click="$set('has','due')" class="chip {{ $has === 'due' ? 'chip-on' : '' }}">Payment due</button>
        </div>
    </div>
    <div class="card overflow-x-auto">
        <table class="tbl">
            <thead><tr><th>Customer</th><th>Contact</th><th>Area</th><th class="text-center">Open complaints</th><th class="text-center">Open quotes</th><th class="text-center">AMC</th><th class="text-right">Balance due</th><th></th></tr></thead>
            <tbody>
            @forelse($customers as $c)
                <tr wire:key="cu{{ $c->id }}">
                    <td><a href="{{ route('customers.show', $c) }}" class="font-medium text-brand-700">{{ $c->name }}</a><div class="text-xs text-slate-500">{{ $c->code }}{{ $c->trn ? ' · TRN '.$c->trn : '' }}</div></td>
                    <td>{{ $c->contact_name ?: '—' }}<div class="text-xs text-slate-500">{{ $c->phone }}</div></td>
                    <td>{{ $c->city }}<div class="text-xs text-slate-500">{{ $c->emirate }}</div></td>
                    <td class="text-center">{{ $c->open_complaints ?: '' }}</td>
                    <td class="text-center">{{ $c->open_quotes ?: '' }}</td>
                    <td class="text-center">@if($c->active_contracts)<x-badge status="active" />@endif</td>
                    <td class="text-right {{ ($c->due - $c->paid) > 0 ? 'font-medium text-red-600' : '' }}">{{ ($c->due - $c->paid) > 0 ? money($c->due - $c->paid) : '' }}</td>
                    <td class="whitespace-nowrap text-right"><a href="{{ route('complaints.create', ['customer' => $c->id]) }}" class="btn btn-sm">+ Complaint</a> <a href="{{ route('enquiries.create', ['customer' => $c->id]) }}" class="btn btn-sm">+ Enquiry</a></td>
                </tr>
            @empty<tr><td colspan="8" class="py-10 text-center text-slate-500">No customers match.</td></tr>@endforelse
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $customers->links() }}</div>
    </div>
</div>

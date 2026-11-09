<div>
    <x-page title="AMC contracts" sub="Renewals due are listed first. Quote the renewal straight from the row.">
        <x-slot:actions><a href="{{ route('quotations.create', ['kind' => 'amc']) }}" class="btn btn-primary">+ AMC quotation</a></x-slot:actions>
    </x-page>
    <div class="card mb-3 card-b flex flex-wrap items-center gap-3">
        <input wire:model.live.debounce.300ms="q" class="input w-full sm:w-64" placeholder="Search contract no, customer, site…">
        <div class="flex flex-wrap gap-1.5">
            <button wire:click="$set('status','active')" class="chip {{ $status === 'active' ? 'chip-on' : '' }}">Active {{ $counts['active'] ?? 0 }}</button>
            <button wire:click="$set('status','due')" class="chip {{ $status === 'due' ? 'chip-on' : '' }}">Renewal due (90 days) {{ $due }}</button>
            <button wire:click="$set('status','expired')" class="chip {{ $status === 'expired' ? 'chip-on' : '' }}">Expired {{ $counts['expired'] ?? 0 }}</button>
            <button wire:click="$set('status','renewed')" class="chip {{ $status === 'renewed' ? 'chip-on' : '' }}">Renewed {{ $counts['renewed'] ?? 0 }}</button>
            <button wire:click="$set('status','all')" class="chip {{ $status === 'all' ? 'chip-on' : '' }}">All</button>
        </div>
    </div>
    <div class="card overflow-x-auto"><table class="tbl">
        <thead><tr><th>Contract</th><th>Customer / site</th><th>Equipment</th><th>Period</th><th>Ends</th><th class="text-center">PPM done</th><th class="text-right">Value</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($contracts as $c)
            <tr wire:key="ct{{ $c->id }}">
                <td><a href="{{ route('contracts.show', $c) }}" class="font-medium text-brand-700">{{ $c->code }}</a></td>
                <td><div class="font-medium">{{ $c->customer->name }}</div><div class="text-xs text-slate-500">{{ $c->site_name }}</div></td>
                <td class="max-w-xs truncate text-slate-600">{{ $c->equipment }}</td>
                <td class="whitespace-nowrap text-slate-600">{{ dmy($c->start_date) }} → {{ dmy($c->end_date) }}</td>
                <td class="whitespace-nowrap {{ $c->status === 'active' && $c->daysLeft() <= 30 ? 'font-medium text-red-600' : '' }}">@if($c->status === 'active'){{ $c->daysLeft() }} days @else — @endif</td>
                <td class="text-center">{{ $c->visits_done }} / {{ $c->visits_per_year }}</td>
                <td class="text-right font-medium">{{ money($c->value) }}</td>
                <td><x-badge :status="$c->status" />@if($c->open_complaints)<div class="text-xs text-slate-500">{{ $c->open_complaints }} open complaint{{ $c->open_complaints > 1 ? 's' : '' }}</div>@endif</td>
                <td class="whitespace-nowrap text-right">@if(in_array($c->status, ['active', 'expired']))<a href="{{ route('contracts.renew', $c) }}" class="btn btn-sm {{ $c->status === 'expired' || $c->daysLeft() <= 90 ? 'btn-primary' : '' }}">Quote renewal</a>@endif <a href="{{ route('complaints.create', ['customer' => $c->customer_id]) }}" class="btn btn-sm">+ Complaint</a></td>
            </tr>
        @empty<tr><td colspan="9" class="py-10 text-center text-slate-500">No contracts here.</td></tr>@endforelse
        </tbody></table><div class="px-4 py-3">{{ $contracts->links() }}</div></div>
</div>

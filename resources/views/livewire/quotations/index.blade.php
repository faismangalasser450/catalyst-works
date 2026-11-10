<div>
    <x-page title="Quotations" sub="Draft → Sent → Approved/Rejected → Order → Invoice. Each step is one button.">
        <x-slot:actions><a href="{{ route('quotations.create') }}" class="btn btn-primary">+ Quotation</a></x-slot:actions>
    </x-page>
    <div class="card mb-3 card-b flex flex-wrap items-center gap-3">
        <input wire:model.live.debounce.300ms="q" class="input w-full sm:w-64" placeholder="Search no, customer, subject…">
        <select wire:model.live="kind" class="input w-auto"><option value="">Sales + AMC</option><option value="sales">Sales</option><option value="amc">AMC</option></select>
        <div class="flex flex-wrap gap-1.5">
            <button wire:click="$set('status','')" class="chip {{ $status === '' ? 'chip-on' : '' }}">Active</button>
            @foreach(\App\Models\Quotation::STATUSES as $k => $l)<button wire:click="$set('status','{{ $k }}')" class="chip {{ $status === $k ? 'chip-on' : '' }}" title="{{ money($counts[$k]->v ?? 0, true) }}"><span class="dot mr-1.5 {{ status_dot($k) }}"></span>{{ $l }} {{ $counts[$k]->c ?? 0 }}</button>@endforeach
            <button wire:click="$set('status','all')" class="chip {{ $status === 'all' ? 'chip-on' : '' }}">All</button>
        </div>
    </div>
    @if($rejecting)
        <form wire:submit="reject" class="card mb-3 card-b flex flex-wrap items-end gap-3 border-red-200">
            <x-field label="Why was it rejected?" name="reason" required class="flex-1"><input wire:model="reason" class="input" placeholder="Price, competitor, postponed…" autofocus></x-field>
            <button class="btn btn-danger">Mark rejected</button><button type="button" wire:click="$set('rejecting', null)" class="btn">Cancel</button>
        </form>
    @endif
    <div class="card overflow-x-auto">
        <table class="tbl">
            <thead><tr><th>No</th><th>Customer</th><th>Subject</th><th>Date</th><th class="text-right">Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($quotations as $q)
                <tr wire:key="q{{ $q->id }}">
                    <td><a href="{{ route('quotations.show', $q) }}" class="font-medium text-brand-700">{{ $q->ref }}</a><div class="text-xs text-slate-500">{{ $q->kind === 'amc' ? 'AMC' : 'Sales' }} · {{ $q->user->name }}</div></td>
                    <td class="font-medium">{{ $q->customer->name }}</td>
                    <td class="max-w-xs truncate text-slate-600">{{ $q->subject }}</td>
                    <td class="whitespace-nowrap">{{ dmy($q->date) }}@if($q->status === 'sent')<div class="text-xs text-slate-500">sent {{ ago($q->sent_at) }} ago</div>@endif</td>
                    <td class="whitespace-nowrap text-right font-medium">{{ money($q->total) }}</td>
                    <td><x-badge :status="$q->status" />@if($q->lost_reason)<div class="max-w-[10rem] truncate text-xs text-slate-500">{{ $q->lost_reason }}</div>@endif</td>
                    <td class="whitespace-nowrap text-right">
                        @if($q->status === 'draft')<button wire:click="markSent({{ $q->id }})" class="btn btn-sm">Mark sent</button>@endif
                        @if($q->isOpen())<button wire:click="approve({{ $q->id }})" class="btn btn-sm btn-ok">Approve</button> <button wire:click="$set('rejecting', {{ $q->id }})" class="btn btn-sm btn-danger">Reject</button>@endif
                        @if($q->status === 'approved')<a href="{{ route('quotations.order', $q) }}" class="btn btn-sm btn-primary">{{ $q->kind === 'amc' ? 'Start AMC' : 'Create order' }}</a>@endif
                        <a href="{{ route('quotations.pdf', $q) }}" target="_blank" class="btn btn-sm">PDF</a>
                    </td>
                </tr>
            @empty<tr><td colspan="7" class="py-10 text-center text-slate-500">No quotations here.</td></tr>@endforelse
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $quotations->links() }}</div>
    </div>
</div>

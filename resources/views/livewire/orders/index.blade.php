<div>
    <x-page title="Orders" sub="Confirmed jobs. Click the coloured status to update progress; invoice when delivered." />
    <div class="card mb-3 card-b flex flex-wrap items-center gap-3">
        <input wire:model.live.debounce.300ms="q" class="input w-full sm:w-64" placeholder="Search order no, LPO, customer…">
        <div class="flex flex-wrap gap-1.5">
            <button wire:click="$set('status','')" class="chip {{ $status === '' ? 'chip-on' : '' }}">Active</button>
            @foreach(\App\Models\Order::STATUSES as $k => $l)<button wire:click="$set('status','{{ $k }}')" class="chip {{ $status === $k ? 'chip-on' : '' }}"><span class="dot mr-1.5 {{ status_dot($k) }}"></span>{{ $l }} {{ $counts[$k] ?? 0 }}</button>@endforeach
            <button wire:click="$set('status','all')" class="chip {{ $status === 'all' ? 'chip-on' : '' }}">All</button>
        </div>
    </div>
    <div class="card overflow-x-auto"><table class="tbl">
        <thead><tr><th>Order</th><th>Customer</th><th>Job</th><th>Due</th><th class="text-right">Value</th><th>Technician</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($orders as $o)
            <tr wire:key="o{{ $o->id }}">
                <td><a href="{{ route('orders.show', $o) }}" class="font-medium text-brand-700">{{ $o->code }}</a><div class="text-xs text-slate-500">{{ $o->po_number ? 'LPO '.$o->po_number : '' }}</div></td>
                <td class="font-medium">{{ $o->customer->name }}</td>
                <td class="max-w-xs truncate text-slate-600">{{ $o->quotation?->subject }}</td>
                <td class="whitespace-nowrap {{ $o->due_date?->isPast() && ! in_array($o->status, ['delivered', 'closed']) ? 'font-medium text-red-600' : '' }}">{{ dmy($o->due_date) }}</td>
                <td class="text-right font-medium">{{ money($o->total) }}</td>
                <td><select wire:change="assign({{ $o->id }}, $event.target.value)" class="input w-40 py-1 text-xs"><option value="">Unassigned</option>@foreach($technicians as $t)<option value="{{ $t->id }}" @selected($o->technician_id === $t->id)>{{ $t->name }}</option>@endforeach</select></td>
                <td><x-status-menu :status="$o->status" :options="\App\Models\Order::STATUSES" :wire="'setStatus('.$o->id.', \'%s\')'" /></td>
                <td class="text-right whitespace-nowrap">@if($o->invoice)<a href="{{ route('invoices.show', $o->invoice) }}" class="btn btn-sm">{{ $o->invoice->code }}</a>@elseif($o->quotation)<a href="{{ route('quotations.invoice', $o->quotation) }}" class="btn btn-sm btn-primary">Invoice</a>@endif</td>
            </tr>
        @empty<tr><td colspan="8" class="py-10 text-center text-slate-500">No orders here.</td></tr>@endforelse
        </tbody></table><div class="px-4 py-3">{{ $orders->links() }}</div></div>
</div>

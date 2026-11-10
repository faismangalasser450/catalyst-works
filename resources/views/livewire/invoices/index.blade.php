<div>
    @php($due = ($counts['unpaid']->due ?? 0) + ($counts['part_paid']->due ?? 0))
    <x-page title="Invoices" sub="AED {{ money($due) }} to collect. Record a payment from the row.">
        <x-slot:actions><a href="{{ route('orders.index') }}" class="btn">Invoice from an order</a></x-slot:actions>
    </x-page>
    <div class="card mb-3 card-b flex flex-wrap items-center gap-3">
        <input wire:model.live.debounce.300ms="q" class="input w-full sm:w-64" placeholder="Search invoice no, LPO, customer…">
        <div class="flex flex-wrap gap-1.5">
            <button wire:click="$set('status','')" class="chip {{ in_array($status, ['', 'unpaid']) ? 'chip-on' : '' }}">To collect {{ ($counts['unpaid']->c ?? 0) + ($counts['part_paid']->c ?? 0) }}</button>
            <button wire:click="$set('status','overdue')" class="chip {{ $status === 'overdue' ? 'chip-on' : '' }}">Overdue</button>
            <button wire:click="$set('status','part_paid')" class="chip {{ $status === 'part_paid' ? 'chip-on' : '' }}">Partly paid {{ $counts['part_paid']->c ?? 0 }}</button>
            <button wire:click="$set('status','paid')" class="chip {{ $status === 'paid' ? 'chip-on' : '' }}">Paid {{ $counts['paid']->c ?? 0 }}</button>
            <button wire:click="$set('status','cancelled')" class="chip {{ $status === 'cancelled' ? 'chip-on' : '' }}">Cancelled {{ $counts['cancelled']->c ?? 0 }}</button>
        </div>
    </div>
    @if($paying)
        <form wire:submit="pay" class="card mb-3 card-b flex flex-wrap items-end gap-3 border-brand-100">
            <x-field label="Amount received (AED)" name="amount" required><input type="number" step="0.01" wire:model="amount" class="input w-40" autofocus></x-field>
            <x-field label="Method" name="method"><select wire:model="method" class="input w-40"><option>Bank transfer</option><option>Cheque</option><option>Cash</option><option>Card</option></select></x-field>
            <x-field label="Reference" name="reference"><input wire:model="reference" class="input w-48" placeholder="Cheque / transfer no"></x-field>
            <button class="btn btn-primary">Record payment</button><button type="button" wire:click="$set('paying', null)" class="btn">Cancel</button>
        </form>
    @endif
    <div class="card overflow-x-auto"><table class="tbl">
        <thead><tr><th>Invoice</th><th>Customer</th><th>Date</th><th>Due</th><th class="text-right">Total</th><th class="text-right">Balance</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($invoices as $i)
            <tr wire:key="i{{ $i->id }}">
                <td><a href="{{ route('invoices.show', $i) }}" class="font-medium text-brand-700">{{ $i->code }}</a><div class="text-xs text-slate-500">{{ $i->lpo_number ? 'LPO '.$i->lpo_number : ($i->kind === 'amc' ? 'AMC' : '') }}</div></td>
                <td class="font-medium">{{ $i->customer->name }}</td>
                <td class="whitespace-nowrap">{{ dmy($i->date) }}</td>
                <td class="whitespace-nowrap {{ $i->isOverdue() ? 'font-medium text-red-600' : '' }}">{{ dmy($i->due_date) }}@if($i->isOverdue())<div class="text-xs">{{ ago($i->due_date) }} late</div>@endif</td>
                <td class="text-right">{{ money($i->total) }}</td>
                <td class="text-right font-medium {{ $i->balance > 0 ? 'text-red-600' : '' }}">{{ $i->balance > 0 ? money($i->balance) : '—' }}</td>
                <td>@if($i->isOverdue())<x-badge status="overdue" :label="$i->status === 'part_paid' ? 'Part paid, overdue' : 'Overdue'" />@else<x-badge :status="$i->status" />@endif</td>
                <td class="whitespace-nowrap text-right">@if($i->balance > 0 && $i->status !== 'cancelled')<button wire:click="startPay({{ $i->id }})" class="btn btn-sm btn-primary">Payment</button>@endif <a href="{{ route('invoices.pdf', $i) }}" target="_blank" class="btn btn-sm">PDF</a></td>
            </tr>
        @empty<tr><td colspan="8" class="py-10 text-center text-slate-500">Nothing here.</td></tr>@endforelse
        </tbody></table><div class="px-4 py-3">{{ $invoices->links() }}</div></div>
</div>

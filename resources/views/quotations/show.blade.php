@php($q = $quotation)
<x-layouts.app :title="$q->ref">
    <x-page :title="$q->ref.' · '.$q->customer->name" :sub="$q->subject" :back="route('quotations.index')">
        <x-slot:actions>
            <x-badge :status="$q->status" />
            <a href="{{ route('quotations.pdf', $q) }}" target="_blank" class="btn">PDF</a>
            @if($q->status === 'draft')<a href="{{ route('quotations.edit', $q) }}" class="btn">Edit</a><form method="post" action="{{ route('quotations.sent', $q) }}">@csrf<button class="btn">Mark sent</button></form>@endif
            @if($q->isOpen())
                <form method="post" action="{{ route('quotations.approve', $q) }}">@csrf<button class="btn btn-primary">Customer approved</button></form>
                <div x-data="{o:false}"><button @click="o=!o" class="btn">Rejected…</button>
                    <form x-show="o" x-cloak method="post" action="{{ route('quotations.reject', $q) }}" class="absolute right-6 z-10 mt-1 card card-b flex w-80 gap-2">@csrf<input name="reason" class="input" placeholder="Reason" required><button class="btn btn-danger">Save</button></form>
                </div>
            @endif
            @if($q->status === 'approved')<a href="{{ route('quotations.order', $q) }}" class="btn btn-primary">{{ $q->kind === 'amc' ? 'Start AMC contract' : 'Create order' }}</a>@endif
            @if(in_array($q->status, ['approved', 'ordered']))<a href="{{ route('quotations.invoice', $q) }}" class="btn">Create invoice</a>@endif
            @if(! in_array($q->status, ['ordered', 'invoiced']))<a href="{{ route('quotations.revise', $q) }}" class="btn">New revision</a>@endif
        </x-slot:actions>
    </x-page>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <div class="card">
                <div class="overflow-x-auto"><table class="tbl">
                    <thead><tr><th>#</th><th>Description</th><th class="text-right">Qty</th><th class="text-right">Unit price</th><th class="text-right">Total</th></tr></thead>
                    <tbody>@foreach($q->items as $it)<tr><td class="text-slate-400">{{ $loop->iteration }}</td><td>{{ trim($it->brand.' '.$it->description) }}@if($it->capacity)<span class="text-slate-500"> · {{ $it->capacity }}</span>@endif</td><td class="text-right">{{ $it->qty + 0 }}</td><td class="text-right">{{ money($it->unit_price) }}</td><td class="text-right font-medium">{{ money($it->total) }}</td></tr>@endforeach</tbody>
                    <tfoot class="text-[13px]">
                        <tr><td colspan="4" class="text-right text-slate-600">Subtotal</td><td class="text-right">{{ money($q->subtotal) }}</td></tr>
                        @if($q->discount_amt > 0)<tr><td colspan="4" class="text-right text-slate-600">Discount {{ $q->discount_pct + 0 }}%</td><td class="text-right">− {{ money($q->discount_amt) }}</td></tr>@endif
                        <tr><td colspan="4" class="text-right text-slate-600">VAT {{ $q->vat_pct + 0 }}%</td><td class="text-right">{{ money($q->vat_amt) }}</td></tr>
                        <tr class="text-base font-semibold"><td colspan="4" class="text-right">Total</td><td class="text-right">AED {{ money($q->total) }}</td></tr>
                    </tfoot>
                </table></div>
            </div>
            <div class="card card-b grid gap-x-6 gap-y-3 text-[13px] sm:grid-cols-2">
                <div><div class="text-xs text-slate-500">Payment</div>{{ $q->payment_terms }}</div><div><div class="text-xs text-slate-500">Delivery</div>{{ $q->delivery_terms }}</div>
                <div><div class="text-xs text-slate-500">Warranty</div>{{ $q->warranty_terms }}</div><div><div class="text-xs text-slate-500">Availability</div>{{ $q->availability }}</div>
                @if($q->notes)<div class="sm:col-span-2"><div class="text-xs text-slate-500">Note</div>{{ $q->notes }}</div>@endif
            </div>
        </div>
        <div class="space-y-4">
            <div class="card card-b text-[13px] space-y-2">
                <div><div class="text-xs text-slate-500">Customer</div><a href="{{ route('customers.show', $q->customer) }}" class="font-medium text-brand-700">{{ $q->customer->name }}</a><div class="text-slate-600">{{ $q->customer->contact_name }} · {{ $q->customer->phone }}</div></div>
                <div><div class="text-xs text-slate-500">Date / valid until</div>{{ dmy($q->date) }} → {{ dmy($q->valid_until) }}</div>
                <div><div class="text-xs text-slate-500">Prepared by</div>{{ $q->user->name }}</div>
                @if($q->enquiry)<div><div class="text-xs text-slate-500">From enquiry</div><a href="{{ route('enquiries.show', $q->enquiry) }}" class="text-brand-700">{{ $q->enquiry->code }}</a></div>@endif
                @if($q->order)<div><div class="text-xs text-slate-500">Order</div><a href="{{ route('orders.show', $q->order) }}" class="text-brand-700">{{ $q->order->code }}</a> <x-badge :status="$q->order->status" /></div>@endif
                @if($q->contract)<div><div class="text-xs text-slate-500">AMC</div><a href="{{ route('contracts.show', $q->contract) }}" class="text-brand-700">{{ $q->contract->code }}</a></div>@endif
                @if($q->invoice)<div><div class="text-xs text-slate-500">Invoice</div><a href="{{ route('invoices.show', $q->invoice) }}" class="text-brand-700">{{ $q->invoice->code }}</a> <x-badge :status="$q->invoice->status" /></div>@endif
                @if($q->lost_reason)<div><div class="text-xs text-slate-500">Rejected</div>{{ $q->lost_reason }}</div>@endif
            </div>
            <div class="card"><div class="card-h">History</div><ol class="px-4 py-2 text-[13px]">@foreach($q->activities as $a)<li class="border-l-2 border-slate-200 py-2 pl-3"><div>{{ ucfirst($a->action) }}{{ $a->note ? ': '.$a->note : '' }}</div><div class="text-xs text-slate-500">{{ $a->user?->name ?? 'System' }} · {{ $a->created_at->format('d-m-Y H:i') }}</div></li>@endforeach</ol></div>
        </div>
    </div>
</x-layouts.app>

@php($i = $invoice)
<x-layouts.app :title="$i->code">
    <x-page :title="$i->code.' · '.$i->customer->name" :sub="'Dated '.dmy($i->date).' · due '.dmy($i->due_date).($i->lpo_number ? ' · LPO '.$i->lpo_number : '')" :back="route('invoices.index')">
        <x-slot:actions>
            <x-badge :status="$i->status" />
            <a href="{{ route('invoices.pdf', $i) }}" target="_blank" class="btn">PDF</a>
            @if($i->status !== 'cancelled' && $i->paid_amount == 0)<form method="post" action="{{ route('invoices.cancel', $i) }}" onsubmit="return confirm('Cancel {{ $i->code }}?')">@csrf<button class="btn btn-danger">Cancel invoice</button></form>@endif
        </x-slot:actions>
    </x-page>
    <div class="grid gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <div class="card"><table class="tbl">
                <thead><tr><th>#</th><th>Description</th><th class="text-right">Qty</th><th class="text-right">Unit price</th><th class="text-right">Total</th></tr></thead>
                <tbody>@foreach($i->items as $it)<tr><td class="text-slate-400">{{ $loop->iteration }}</td><td>{{ $it->description }}</td><td class="text-right">{{ $it->qty + 0 }}</td><td class="text-right">{{ money($it->unit_price) }}</td><td class="text-right font-medium">{{ money($it->total) }}</td></tr>@endforeach</tbody>
                <tfoot><tr><td colspan="4" class="text-right text-slate-600">Subtotal</td><td class="text-right">{{ money($i->subtotal) }}</td></tr>@if($i->discount_amt > 0)<tr><td colspan="4" class="text-right text-slate-600">Discount</td><td class="text-right">− {{ money($i->discount_amt) }}</td></tr>@endif<tr><td colspan="4" class="text-right text-slate-600">VAT {{ $i->vat_pct + 0 }}%</td><td class="text-right">{{ money($i->vat_amt) }}</td></tr><tr class="text-base font-semibold"><td colspan="4" class="text-right">Total</td><td class="text-right">AED {{ money($i->total) }}</td></tr><tr><td colspan="4" class="text-right text-slate-600">Paid</td><td class="text-right">{{ money($i->paid_amount) }}</td></tr><tr class="font-semibold {{ $i->balance > 0 ? 'text-red-600' : 'text-green-700' }}"><td colspan="4" class="text-right">Balance</td><td class="text-right">{{ money($i->balance) }}</td></tr></tfoot>
            </table></div>
            @if($i->balance > 0 && $i->status !== 'cancelled')
            <form method="post" action="{{ route('invoices.pay', $i) }}" class="card">@csrf
                <div class="card-h">Record a payment</div>
                <div class="card-b grid gap-3 sm:grid-cols-5">
                    <x-field label="Date" name="date" required><input type="date" name="date" class="input" value="{{ today()->format('Y-m-d') }}"></x-field>
                    <x-field label="Amount (AED)" name="amount" required><input type="number" step="0.01" min="0.01" max="{{ $i->balance }}" name="amount" class="input" value="{{ $i->balance }}"></x-field>
                    <x-field label="Method" name="method"><select name="method" class="input"><option>Bank transfer</option><option>Cheque</option><option>Cash</option><option>Card</option></select></x-field>
                    <x-field label="Reference" name="reference"><input name="reference" class="input" placeholder="Cheque / transfer no"></x-field>
                    <div class="flex items-end"><button class="btn btn-primary w-full justify-center">Save payment</button></div>
                </div>
            </form>
            @endif
        </div>
        <div class="space-y-4">
            <div class="card card-b space-y-2 text-[13px]">
                <div><div class="text-xs text-slate-500">Customer</div><a href="{{ route('customers.show', $i->customer) }}" class="font-medium text-brand-700">{{ $i->customer->name }}</a>@if($i->customer->trn)<div class="text-slate-600">TRN {{ $i->customer->trn }}</div>@endif</div>
                @if($i->quotation)<div><div class="text-xs text-slate-500">Quotation</div><a href="{{ route('quotations.show', $i->quotation) }}" class="text-brand-700">{{ $i->quotation->ref }}</a></div>@endif
                @if($i->order)<div><div class="text-xs text-slate-500">Order</div><a href="{{ route('orders.show', $i->order) }}" class="text-brand-700">{{ $i->order->code }}</a></div>@endif
                @if($i->contract)<div><div class="text-xs text-slate-500">AMC</div><a href="{{ route('contracts.show', $i->contract) }}" class="text-brand-700">{{ $i->contract->code }}</a></div>@endif
            </div>
            <div class="card"><div class="card-h">Payments</div><table class="tbl"><tbody>@forelse($i->payments as $p)<tr><td>{{ dmy($p->date) }}</td><td class="text-slate-600">{{ $p->method }} {{ $p->reference }}</td><td class="text-right font-medium">{{ money($p->amount) }}</td></tr>@empty<tr><td class="py-6 text-center text-slate-500">No payment yet.</td></tr>@endforelse</tbody></table></div>
            <div class="card"><div class="card-h">History</div><ol class="px-4 py-2 text-[13px]">@foreach($i->activities as $a)<li class="border-l-2 border-slate-200 py-2 pl-3"><div>{{ ucfirst($a->action) }}{{ $a->note ? ': '.$a->note : '' }}</div><div class="text-xs text-slate-500">{{ $a->user?->name ?? 'System' }} · {{ $a->created_at->format('d-m-Y H:i') }}</div></li>@endforeach</ol></div>
        </div>
    </div>
</x-layouts.app>

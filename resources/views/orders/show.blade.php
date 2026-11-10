@php($o = $order)
<x-layouts.app :title="$o->code">
    <x-page :title="$o->code.' · '.$o->customer->name" :sub="$o->quotation?->subject" :back="route('orders.index')">
        <x-slot:actions>
            <x-status-menu :status="$o->status" :options="\App\Models\Order::STATUSES" :action="route('orders.status', $o)" />
            @php($next = ['confirmed' => 'in_progress', 'in_progress' => 'delivered', 'delivered' => 'closed'][$o->status] ?? null)
            @if($next)<form method="post" action="{{ route('orders.status', $o) }}">@csrf<input type="hidden" name="status" value="{{ $next }}"><button class="btn {{ status_solid($next) }}">Mark {{ strtolower(\App\Models\Order::STATUSES[$next]) }}</button></form>@endif
            @if($o->invoice)<a href="{{ route('invoices.show', $o->invoice) }}" class="btn">Invoice {{ $o->invoice->code }}</a>@elseif($o->quotation)<a href="{{ route('quotations.invoice', $o->quotation) }}" class="btn btn-primary">Create invoice</a>@endif
        </x-slot:actions>
    </x-page>
    <div class="grid gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <div class="card"><div class="card-h">Scope <a href="{{ route('quotations.show', $o->quotation) }}" class="text-[13px] font-normal text-brand-700">{{ $o->quotation?->ref }}</a></div>
                <table class="tbl"><tbody>@foreach($o->quotation?->items ?? [] as $it)<tr><td>{{ trim($it->brand.' '.$it->description) }}{{ $it->capacity ? ' · '.$it->capacity : '' }}</td><td class="text-right">{{ $it->qty + 0 }}</td><td class="text-right">{{ money($it->total) }}</td></tr>@endforeach<tr class="font-semibold"><td colspan="2" class="text-right">Order value (incl. VAT)</td><td class="text-right">AED {{ money($o->total) }}</td></tr></tbody></table>
            </div>
            <form method="post" action="{{ route('orders.update', $o) }}" class="card">@csrf @method('put')
                <div class="card-h">Job details <button class="btn btn-sm">Save</button></div>
                <div class="card-b grid gap-4 sm:grid-cols-3">
                    <x-field label="Customer LPO no" name="po_number"><input name="po_number" class="input" value="{{ old('po_number', $o->po_number) }}"></x-field>
                    <x-field label="LPO date" name="po_date"><input type="date" name="po_date" class="input" value="{{ old('po_date', $o->po_date?->format('Y-m-d')) }}"></x-field>
                    <x-field label="Technician / team lead" name="technician_id"><select name="technician_id" class="input"><option value="">Unassigned</option>@foreach($technicians as $t)<option value="{{ $t->id }}" @selected($o->technician_id === $t->id)>{{ $t->name }}</option>@endforeach</select></x-field>
                    <x-field label="Start" name="start_date"><input type="date" name="start_date" class="input" value="{{ old('start_date', $o->start_date?->format('Y-m-d')) }}"></x-field>
                    <x-field label="Due" name="due_date"><input type="date" name="due_date" class="input" value="{{ old('due_date', $o->due_date?->format('Y-m-d')) }}"></x-field>
                    <x-field label="Delivered on" name="delivered_on"><input class="input" disabled value="{{ dmy($o->delivered_on) }}"></x-field>
                    <x-field label="Notes" name="notes" class="sm:col-span-3"><input name="notes" class="input" value="{{ old('notes', $o->notes) }}" placeholder="Site access, material status, pending items…"></x-field>
                </div>
            </form>
        </div>
        <div class="card self-start"><div class="card-h">History</div><ol class="px-4 py-2 text-[13px]">@foreach($o->activities as $a)<li class="border-l-2 border-slate-200 py-2 pl-3"><div>{{ ucfirst($a->action) }}{{ $a->note ? ': '.$a->note : '' }}</div><div class="text-xs text-slate-500">{{ $a->user?->name ?? 'System' }} · {{ $a->created_at->format('d-m-Y H:i') }}</div></li>@endforeach</ol></div>
    </div>
</x-layouts.app>

@php($c = $customer)
<x-layouts.app :title="$c->name">
    <x-page :title="$c->name" :sub="collect([$c->contact_name, $c->phone, $c->full_address, $c->trn ? 'TRN '.$c->trn : null])->filter()->implode(' · ')" :back="route('customers.index')">
        <x-slot:actions>
            <a href="{{ route('complaints.create', ['customer' => $c->id]) }}" class="btn btn-primary">+ Complaint</a>
            <a href="{{ route('enquiries.create', ['customer' => $c->id]) }}" class="btn">+ Enquiry</a>
            <a href="{{ route('quotations.create', ['customer' => $c->id]) }}" class="btn">+ Quotation</a>
            <a href="{{ route('customers.edit', $c) }}" class="btn">Edit</a>
        </x-slot:actions>
    </x-page>
    @if($c->notes)<div class="mb-4 rounded-md border border-amber-200 bg-amber-50 px-4 py-2 text-[13px] text-amber-900">{{ $c->notes }}</div>@endif

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="card">
            <div class="card-h">Complaints <span class="text-xs font-normal text-slate-500">{{ $c->complaints->where('status', '!=', 'closed')->count() }} open · {{ $c->complaints->count() }} total</span></div>
            <table class="tbl"><tbody>
                @forelse($c->complaints->take(8) as $x)<tr><td><a href="{{ route('complaints.show', $x) }}" class="font-medium text-brand-700">{{ $x->code }}</a></td><td>{{ $x->type }}<div class="text-xs text-slate-500">{{ $x->location }}</div></td><td class="text-xs text-slate-500">{{ dmy($x->reported_at) }}</td><td>{{ $x->technician?->name }}</td><td><x-badge :status="$x->status" /></td></tr>
                @empty<tr><td class="py-6 text-center text-slate-500">No complaints.</td></tr>@endforelse
            </tbody></table>
        </div>
        <div class="card">
            <div class="card-h">AMC contracts</div>
            <table class="tbl"><tbody>
                @forelse($c->contracts as $x)<tr><td><a href="{{ route('contracts.show', $x) }}" class="font-medium text-brand-700">{{ $x->code }}</a><div class="text-xs text-slate-500">{{ $x->equipment }}</div></td><td class="text-xs text-slate-500">{{ dmy($x->start_date) }} → {{ dmy($x->end_date) }}</td><td class="text-right">{{ money($x->value) }}</td><td><x-badge :status="$x->status" /></td></tr>
                @empty<tr><td class="py-6 text-center text-slate-500">No AMC. <a href="{{ route('quotations.create', ['customer' => $c->id, 'kind' => 'amc']) }}" class="text-brand-700">Quote one</a>.</td></tr>@endforelse
            </tbody></table>
        </div>
        <div class="card">
            <div class="card-h">Enquiries & quotations</div>
            <table class="tbl"><tbody>
                @forelse($c->quotations->take(8) as $x)<tr><td><a href="{{ route('quotations.show', $x) }}" class="font-medium text-brand-700">{{ $x->ref }}</a><div class="max-w-xs truncate text-xs text-slate-500">{{ $x->subject }}</div></td><td class="text-xs text-slate-500">{{ dmy($x->date) }}</td><td class="text-right">{{ money($x->total) }}</td><td><x-badge :status="$x->status" /></td></tr>
                @empty<tr><td class="py-6 text-center text-slate-500">No quotations yet.</td></tr>@endforelse
                @foreach($c->enquiries->where('status', 'open') as $e)<tr><td><a href="{{ route('enquiries.show', $e) }}" class="font-medium text-brand-700">{{ $e->code }}</a><div class="text-xs text-slate-500">{{ $e->type }}</div></td><td class="text-xs text-slate-500">{{ dmy($e->date) }}</td><td></td><td><x-badge status="open" label="Enquiry open" /></td></tr>@endforeach
            </tbody></table>
        </div>
        <div class="card">
            <div class="card-h">Invoices <span class="text-xs font-normal {{ $c->invoices->sum('balance') > 0 ? 'text-red-600' : 'text-slate-500' }}">{{ money($c->invoices->sum('balance'), true) }} due</span></div>
            <table class="tbl"><tbody>
                @forelse($c->invoices->take(8) as $x)<tr><td><a href="{{ route('invoices.show', $x) }}" class="font-medium text-brand-700">{{ $x->code }}</a></td><td class="text-xs text-slate-500">{{ dmy($x->date) }}</td><td class="text-right">{{ money($x->total) }}</td><td class="text-right {{ $x->balance > 0 ? 'text-red-600' : '' }}">{{ $x->balance > 0 ? money($x->balance).' due' : '' }}</td><td><x-badge :status="$x->status" /></td></tr>
                @empty<tr><td class="py-6 text-center text-slate-500">No invoices.</td></tr>@endforelse
            </tbody></table>
        </div>
    </div>
</x-layouts.app>

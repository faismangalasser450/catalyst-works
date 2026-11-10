<x-layouts.app :title="$c->code">
    <x-page :title="$c->code.' · '.$c->customer->name" :sub="$c->site_name.' · '.dmy($c->start_date).' → '.dmy($c->end_date).($c->status === 'active' ? ' · '.$c->daysLeft().' days left' : '')" :back="route('contracts.index')">
        <x-slot:actions>
            <x-badge :status="$c->status" />
            <a href="{{ route('complaints.create', ['customer' => $c->customer_id]) }}" class="btn">+ Complaint</a>
            @if(in_array($c->status, ['active', 'expired']))<a href="{{ route('contracts.renew', $c) }}" class="btn btn-primary">Quote renewal</a>@endif
            @if($c->status === 'active')<form method="post" action="{{ route('contracts.cancel', $c) }}" onsubmit="return confirm('Cancel {{ $c->code }}?')">@csrf<button class="btn btn-danger">Cancel</button></form>@endif
        </x-slot:actions>
    </x-page>
    <div class="grid gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <div class="card"><div class="card-h">PPM visits <span class="text-xs font-normal text-slate-500">{{ $c->visits->where('status', 'done')->count() }} of {{ $c->visits->count() }} done</span></div>
                <table class="tbl"><thead><tr><th>Planned</th><th>Done</th><th>Technician</th><th>Notes</th><th>Status</th><th></th></tr></thead><tbody>
                @foreach($c->visits as $v)
                    <tr><td>{{ dmy($v->scheduled_on) }}</td><td>{{ dmy($v->done_on) }}</td><td>{{ $v->technician?->name ?? '—' }}</td><td class="text-slate-600">{{ $v->notes }}</td><td><x-badge :status="$v->status === 'due' && $v->scheduled_on->isPast() ? 'missed' : $v->status" :label="$v->status === 'due' && $v->scheduled_on->isPast() ? 'Overdue' : null" /></td>
                    <td class="text-right">@if($v->status !== 'done')<form method="post" action="{{ route('ppm.done', $v) }}" class="flex justify-end gap-1">@csrf<select name="technician_id" class="input w-36 py-1 text-xs"><option value="">Technician…</option>@foreach($technicians as $t)<option value="{{ $t->id }}" @selected($v->technician_id === $t->id)>{{ $t->name }}</option>@endforeach</select><button class="btn btn-sm btn-primary">Done today</button></form>@endif</td></tr>
                @endforeach
                </tbody></table>
            </div>
            <div class="card"><div class="card-h">Complaints under this contract <span class="text-xs font-normal text-slate-500">{{ $c->complaints->count() }}</span></div>
                <table class="tbl"><tbody>@forelse($c->complaints->take(10) as $x)<tr><td><a href="{{ route('complaints.show', $x) }}" class="font-medium text-brand-700">{{ $x->code }}</a></td><td>{{ $x->type }}<div class="text-xs text-slate-500">{{ $x->location }}</div></td><td class="text-xs text-slate-500">{{ dmy($x->reported_at) }}</td><td>{{ $x->technician?->name }}</td><td><x-badge :status="$x->status" /></td></tr>@empty<tr><td class="py-6 text-center text-slate-500">No complaints. Good sign.</td></tr>@endforelse</tbody></table>
            </div>
            <form method="post" action="{{ route('contracts.update', $c) }}" class="card">@csrf @method('put')
                <div class="card-h">Contract details <button class="btn btn-sm">Save</button></div>
                <div class="card-b grid gap-4 sm:grid-cols-3">
                    <x-field label="Site" name="site_name" required class="sm:col-span-2"><input name="site_name" class="input" value="{{ old('site_name', $c->site_name) }}"></x-field>
                    <x-field label="Value (AED, incl. VAT)" name="value" required><input type="number" step="0.01" name="value" class="input" value="{{ old('value', $c->value) }}"></x-field>
                    <x-field label="Equipment covered" name="equipment" required class="sm:col-span-3"><input name="equipment" class="input" value="{{ old('equipment', $c->equipment) }}"></x-field>
                    <x-field label="Start" name="start_date" required><input type="date" name="start_date" class="input" value="{{ old('start_date', $c->start_date->format('Y-m-d')) }}"></x-field>
                    <x-field label="End" name="end_date" required><input type="date" name="end_date" class="input" value="{{ old('end_date', $c->end_date->format('Y-m-d')) }}"></x-field>
                    <x-field label="PPM visits per year" name="visits_per_year" required hint="Changing this re-plans visits if none are done yet"><input type="number" name="visits_per_year" class="input" value="{{ old('visits_per_year', $c->visits_per_year) }}"></x-field>
                    <x-field label="Notes" name="notes" class="sm:col-span-3"><input name="notes" class="input" value="{{ old('notes', $c->notes) }}"></x-field>
                </div>
            </form>
        </div>
        <div class="space-y-4">
            <div class="card card-b space-y-2 text-[13px]">
                <div><div class="text-xs text-slate-500">Customer</div><a href="{{ route('customers.show', $c->customer) }}" class="font-medium text-brand-700">{{ $c->customer->name }}</a><div class="text-slate-600">{{ $c->customer->contact_name }} · {{ $c->customer->phone }}</div></div>
                @if($c->quotation)<div><div class="text-xs text-slate-500">Quotation</div><a href="{{ route('quotations.show', $c->quotation) }}" class="text-brand-700">{{ $c->quotation->ref }}</a></div>@endif
                <div><div class="text-xs text-slate-500">Invoices</div>@forelse($c->invoices as $i)<div><a href="{{ route('invoices.show', $i) }}" class="text-brand-700">{{ $i->code }}</a> {{ money($i->total) }} <x-badge :status="$i->status" /></div>@empty <span class="text-slate-500">None yet.</span> @if($c->quotation && in_array($c->quotation->status, ['approved', 'ordered']))<a href="{{ route('quotations.invoice', $c->quotation) }}" class="text-brand-700">Create invoice</a>@endif @endforelse</div>
            </div>
            <div class="card"><div class="card-h">History</div><ol class="px-4 py-2 text-[13px]">@foreach($c->activities as $a)<li class="border-l-2 border-slate-200 py-2 pl-3"><div>{{ ucfirst($a->action) }}{{ $a->note ? ': '.$a->note : '' }}</div><div class="text-xs text-slate-500">{{ $a->user?->name ?? 'System' }} · {{ $a->created_at->format('d-m-Y H:i') }}</div></li>@endforeach</ol></div>
        </div>
    </div>
</x-layouts.app>

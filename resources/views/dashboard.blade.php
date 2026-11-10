<x-layouts.app title="Dashboard">
@php($u = auth()->user())
<x-page title="Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ explode(' ', $u->name)[0] }}" sub="{{ now()->format('l, d F Y') }}">
    <x-slot:actions>
        @if($u->hasRole('sales','service'))<a href="{{ route('complaints.create') }}" class="btn btn-primary">+ Complaint</a>@endif
        @if($u->hasRole('sales'))<a href="{{ route('enquiries.create') }}" class="btn">+ Enquiry</a><a href="{{ route('quotations.create') }}" class="btn">+ Quotation</a>@endif
        @if($u->hasRole('sales','service'))<a href="{{ route('customers.create') }}" class="btn">+ Customer</a>@endif
    </x-slot:actions>
</x-page>

{{-- KPIs --}}
<div class="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-8">
    <a href="{{ route('complaints.index', ['status' => 'open']) }}" class="kpi"><div class="l">Open complaints</div><div class="v">{{ $kpi['open'] }}</div><div class="s {{ $kpi['overdue'] ? 'text-red-600' : '' }}">{{ $kpi['overdue'] }} older than {{ $sla }} h · {{ $kpi['unassigned'] }} unassigned</div></a>
    <div class="kpi"><div class="l">Closed this month</div><div class="v">{{ $kpi['closed_this'] }}</div><div class="s">{{ $kpi['closed_last'] }} last month</div></div>
    <a href="{{ route('ppm.index') }}" class="kpi"><div class="l">PPM visits this month</div><div class="v">{{ $kpi['ppm_done'] }} <span class="text-base font-normal text-slate-400">/ {{ $kpi['ppm_done'] + $kpi['ppm_due'] }}</span></div><div class="s {{ $kpi['ppm_missed'] ? 'text-red-600' : '' }}">{{ $kpi['ppm_missed'] }} overdue</div></a>
    <a href="{{ route('contracts.index') }}" class="kpi"><div class="l">AMC renewals due</div><div class="v">{{ $kpi['renew30'] }}</div><div class="s">in 30 days · {{ $kpi['renew90'] }} in 90 days</div></a>
    <a href="{{ route('enquiries.index') }}" class="kpi"><div class="l">Enquiries this month</div><div class="v">{{ $kpi['enq_this'] }}</div><div class="s">{{ $kpi['enq_open'] }} still open</div></a>
    <a href="{{ route('quotations.index', ['status' => 'sent']) }}" class="kpi"><div class="l">Quotations sent</div><div class="v">{{ $kpi['q_sent_this'] }}</div><div class="s">{{ money($kpi['q_sent_value'], true) }} this month</div></a>
    <div class="kpi"><div class="l">Won rate, 90 days</div><div class="v">{{ $kpi['conv_pct'] }}%</div><div class="s">{{ $kpi['conv_won'] }} of {{ $kpi['conv_total'] }} decided</div></div>
    <a href="{{ route('invoices.index', ['status' => 'unpaid']) }}" class="kpi"><div class="l">To collect</div><div class="v">{{ money($kpi['outstanding'], true) }}</div><div class="s {{ $kpi['overdue_inv'] ? 'text-red-600' : '' }}">{{ $kpi['overdue_inv'] }} invoices overdue</div></a>
</div>

<div class="mt-4 grid gap-4 xl:grid-cols-3">
    {{-- Open complaints --}}
    <div class="card xl:col-span-2">
        <div class="card-h">Open complaints, oldest first <a href="{{ route('complaints.index') }}" class="text-[13px] font-normal text-brand-700">See all</a></div>
        <div class="overflow-x-auto"><table class="tbl">
            <thead><tr><th>No</th><th>Customer</th><th>Problem</th><th>Age</th><th>Technician</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($openList as $c)
                <tr>
                    <td><a href="{{ route('complaints.show', $c) }}" class="font-medium text-brand-700">{{ $c->code }}</a>@if($c->priority==='high') <x-badge status="high" label="High" />@endif</td>
                    <td>{{ $c->customer->name }}<div class="text-xs text-slate-500">{{ $c->location }}</div></td>
                    <td>{{ $c->type }}<div class="text-xs text-slate-500">{{ $c->equipment }}</div></td>
                    <td class="{{ $c->isOverdue() ? 'font-medium text-red-600' : '' }}">{{ ago($c->reported_at) }}</td>
                    <td>
                        @if($u->hasRole('sales','service'))
                        <form method="post" action="{{ route('complaints.assign', $c) }}">@csrf
                            <select name="technician_id" class="input py-1 text-xs" onchange="this.form.submit()">
                                <option value="">Unassigned</option>
                                @foreach($technicians as $t)<option value="{{ $t->id }}" @selected($c->technician_id === $t->id)>{{ $t->name }}</option>@endforeach
                            </select>
                        </form>
                        @else {{ $c->technician?->name ?? '—' }} @endif
                    </td>
                    <td>
                        @if($u->hasRole('sales','service'))
                        <x-status-menu :status="$c->status" :options="\App\Models\Complaint::STATUSES" :action="route('complaints.status', $c)" :links="['closed' => route('complaints.show', [$c, 'close' => 1])]" />
                        @else <x-badge :status="$c->status" /> @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-8 text-center text-slate-500">No open complaints. Nice.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div>

    {{-- Complaints chart --}}
    <div class="card">
        <div class="card-h">Complaints per week <span class="text-xs font-normal text-slate-500">received vs closed, 12 weeks</span></div>
        <div class="card-b">
            @php($max = max(1, collect($weeks)->max(fn ($w) => max($w['in'], $w['out']))))
            <div class="flex h-36 items-end gap-1.5">
                @foreach($weeks as $w)
                    <div class="flex h-full flex-1 items-end gap-px" title="{{ $w['label'] }}: {{ $w['in'] }} received, {{ $w['out'] }} closed">
                        <div class="flex-1 rounded-t bg-brand-600" style="height: {{ $w['in'] / $max * 100 }}%"></div>
                        <div class="flex-1 rounded-t bg-green-500" style="height: {{ $w['out'] / $max * 100 }}%"></div>
                    </div>
                @endforeach
            </div>
            <div class="mt-1 flex justify-between text-[11px] text-slate-500"><span>{{ $weeks[0]['label'] }}</span><span>{{ end($weeks)['label'] }}</span></div>
            <div class="mt-2 flex gap-4 text-xs text-slate-600"><span><span class="inline-block h-2.5 w-2.5 rounded-sm bg-brand-600"></span> Received</span><span><span class="inline-block h-2.5 w-2.5 rounded-sm bg-green-500"></span> Closed</span></div>

            <div class="mt-5 border-t border-slate-100 pt-4">
                <div class="mb-2 font-semibold">Sales funnel, last 90 days</div>
                @php($fmax = max(1, max($funnel)))
                @foreach($funnel as $label => $n)
                    <div class="mb-1.5 flex items-center gap-2 text-xs"><span class="w-16 text-slate-600">{{ $label }}</span><div class="h-4 flex-1 rounded bg-slate-100"><div class="h-4 rounded bg-brand-600/80" style="width: {{ $n / $fmax * 100 }}%"></div></div><span class="w-6 text-right font-medium">{{ $n }}</span></div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="card">
        <div class="card-h">Quotations awaiting answer</div>
        <ul class="divide-y divide-slate-100 text-[13px]">
            @forelse($awaiting as $q)<li class="flex items-center justify-between gap-2 px-4 py-2"><div><a href="{{ route('quotations.show', $q) }}" class="font-medium text-brand-700">{{ $q->code }}</a><div class="text-xs text-slate-500">{{ $q->customer->name }} · sent {{ ago($q->sent_at) }} ago</div></div><div class="font-medium">{{ money($q->total) }}</div></li>
            @empty<li class="px-4 py-6 text-center text-slate-500">Nothing waiting.</li>@endforelse
        </ul>
    </div>
    <div class="card">
        <div class="card-h">{{ $u->role === 'sales' ? 'My open enquiries' : 'Open enquiries' }}</div>
        <ul class="divide-y divide-slate-100 text-[13px]">
            @forelse($followUps as $e)<li class="px-4 py-2"><a href="{{ route('enquiries.show', $e) }}" class="font-medium text-brand-700">{{ $e->customer->name }}</a><div class="text-xs text-slate-500">{{ $e->type }} · {{ ago($e->date) }} ago · {{ $e->user->name }}</div></li>
            @empty<li class="px-4 py-6 text-center text-slate-500">All enquiries are quoted.</li>@endforelse
        </ul>
    </div>
    <div class="card">
        <div class="card-h">AMC renewals, next 90 days</div>
        <ul class="divide-y divide-slate-100 text-[13px]">
            @forelse($renewals as $c)<li class="flex items-center justify-between gap-2 px-4 py-2"><div><a href="{{ route('contracts.show', $c) }}" class="font-medium text-brand-700">{{ $c->customer->name }}</a><div class="text-xs text-slate-500">{{ $c->equipment }}</div></div><div class="text-right"><div class="font-medium {{ $c->daysLeft() <= 30 ? 'text-red-600' : '' }}">{{ $c->daysLeft() }} days</div><div class="text-xs text-slate-500">{{ dmy($c->end_date) }}</div></div></li>
            @empty<li class="px-4 py-6 text-center text-slate-500">No renewals due.</li>@endforelse
        </ul>
    </div>
    <div class="card">
        <div class="card-h">Overdue invoices</div>
        <ul class="divide-y divide-slate-100 text-[13px]">
            @forelse($overdueInvoices as $i)<li class="flex items-center justify-between gap-2 px-4 py-2"><div><a href="{{ route('invoices.show', $i) }}" class="font-medium text-brand-700">{{ $i->code }}</a><div class="text-xs text-slate-500">{{ $i->customer->name }} · due {{ dmy($i->due_date) }}</div></div><div class="font-medium text-red-600">{{ money($i->balance) }}</div></li>
            @empty<li class="px-4 py-6 text-center text-slate-500">Nothing overdue.</li>@endforelse
        </ul>
    </div>
</div>
</x-layouts.app>

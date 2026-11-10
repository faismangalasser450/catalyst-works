@php($e = $enquiry)
<x-layouts.app :title="$e->code">
    <x-page :title="$e->code.' · '.$e->customer->name" :sub="$e->type.' · '.dmy($e->date).' · via '.$e->source.' · '.$e->user->name" :back="route('enquiries.index')">
        <x-slot:actions>
            <x-badge :status="$e->status" kind="enquiry" />
            @if($e->status === 'open')<a href="{{ route('quotations.create', ['enquiry' => $e->id]) }}" class="btn btn-primary">Create quotation</a>@endif
            <a href="{{ route('enquiries.edit', $e) }}" class="btn">Edit</a>
        </x-slot:actions>
    </x-page>
    <div class="grid gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <div class="card"><div class="card-h">Requirement</div><div class="card-b text-[13px]">{{ $e->description }}@if($e->client_ref)<div class="mt-2 text-xs text-slate-500">Customer ref: {{ $e->client_ref }}</div>@endif @if($e->follow_up_on)<div class="mt-1 text-xs text-slate-500">Follow up on {{ dmy($e->follow_up_on) }}</div>@endif</div></div>
            <div class="card"><div class="card-h">Quotations</div>
                <table class="tbl"><tbody>
                @forelse($e->quotations as $q)<tr><td><a href="{{ route('quotations.show', $q) }}" class="font-medium text-brand-700">{{ $q->ref }}</a></td><td class="text-xs text-slate-500">{{ dmy($q->date) }}</td><td class="text-right">{{ money($q->total) }}</td><td><x-badge :status="$q->status" /></td></tr>
                @empty<tr><td class="py-6 text-center text-slate-500">Not quoted yet.</td></tr>@endforelse
                </tbody></table>
            </div>
        </div>
        <div class="card self-start"><div class="card-h">Customer</div><div class="card-b text-[13px]"><a href="{{ route('customers.show', $e->customer) }}" class="font-medium text-brand-700">{{ $e->customer->name }}</a><div class="text-slate-600">{{ $e->customer->contact_name }} · {{ $e->customer->phone }}</div><div class="text-slate-600">{{ $e->customer->full_address }}</div></div></div>
    </div>
</x-layouts.app>

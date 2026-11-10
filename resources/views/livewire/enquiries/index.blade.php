<div>
    <x-page title="Enquiries" sub="Every lead that came in. Quote it from the row in one click.">
        <x-slot:actions><a href="{{ route('enquiries.create') }}" class="btn btn-primary">+ Enquiry</a></x-slot:actions>
    </x-page>
    <div class="card mb-3 card-b flex flex-wrap items-center gap-3">
        <input wire:model.live.debounce.300ms="q" class="input w-full sm:w-64" placeholder="Search customer, no, description…">
        <select wire:model.live="owner" class="input w-auto"><option value="">Everyone</option>@foreach($owners as $o)<option value="{{ $o->id }}">{{ $o->name }}</option>@endforeach</select>
        <div class="flex flex-wrap gap-1.5">
            @foreach(\App\Models\Enquiry::STATUSES as $k => $l)<button wire:click="$set('status','{{ $k }}')" class="chip {{ $status === $k ? 'chip-on' : '' }}">{{ $l }} {{ $counts[$k] ?? 0 }}</button>@endforeach
            <button wire:click="$set('status','all')" class="chip {{ $status === 'all' ? 'chip-on' : '' }}">All</button>
        </div>
    </div>
    <div class="card overflow-x-auto">
        <table class="tbl">
            <thead><tr><th>No</th><th>Customer</th><th>Requirement</th><th>Date</th><th>Source</th><th>Sales</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($enquiries as $e)
                <tr wire:key="e{{ $e->id }}">
                    <td><a href="{{ route('enquiries.show', $e) }}" class="font-medium text-brand-700">{{ $e->code }}</a></td>
                    <td class="font-medium">{{ $e->customer->name }}</td>
                    <td>{{ $e->type }}<div class="max-w-xs truncate text-xs text-slate-500">{{ $e->description }}</div></td>
                    <td class="whitespace-nowrap">{{ dmy($e->date) }}<div class="text-xs text-slate-500">{{ ago($e->date) }} ago</div></td>
                    <td class="text-slate-600">{{ $e->source }}</td>
                    <td class="text-slate-600">{{ $e->user->name }}</td>
                    <td><x-badge :status="$e->status" kind="enquiry" />@if($e->quotations_count)<div class="text-xs text-slate-500">{{ $e->quotations_count }} quote{{ $e->quotations_count > 1 ? 's' : '' }}</div>@endif</td>
                    <td class="whitespace-nowrap text-right">
                        @if($e->status === 'open')<a href="{{ route('quotations.create', ['enquiry' => $e->id]) }}" class="btn btn-sm btn-primary">Quote</a> <button wire:click="markLost({{ $e->id }})" wire:confirm="Mark {{ $e->code }} as lost?" class="btn btn-sm btn-danger">Lost</button>@endif
                    </td>
                </tr>
            @empty<tr><td colspan="8" class="py-10 text-center text-slate-500">No enquiries here.</td></tr>@endforelse
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $enquiries->links() }}</div>
    </div>
</div>

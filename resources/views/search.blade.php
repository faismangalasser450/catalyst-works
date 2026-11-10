<x-layouts.app title="Search">
    <x-page title="Results for “{{ $q }}”" :sub="count($results) ? null : 'Nothing matched. Try a customer name, phone, or document number.'" />
    <div class="grid gap-4 md:grid-cols-2">
        @foreach($results as $group => $rows)
            <div class="card"><div class="card-h">{{ $group }} <span class="text-xs font-normal text-slate-500">{{ $rows->count() }}</span></div>
                <ul class="divide-y divide-slate-100">@foreach($rows as [$url, $title, $sub])<li><a href="{{ $url }}" class="block px-4 py-2.5 hover:bg-slate-50"><div class="font-medium text-brand-700">{{ $title }}</div><div class="text-xs text-slate-500">{{ $sub }}</div></a></li>@endforeach</ul>
            </div>
        @endforeach
    </div>
</x-layouts.app>

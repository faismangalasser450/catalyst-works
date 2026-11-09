@php($u = auth()->user())
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Catalyst Works' }} · Catalyst Works</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ nav: false }" class="min-h-screen">
<div class="flex min-h-screen">
    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 w-56 transform bg-brand-800 text-white transition lg:static lg:translate-x-0" :class="nav ? 'translate-x-0' : '-translate-x-full'" x-cloak>
        <div class="flex h-14 items-center gap-2 px-4">
            <div class="flex h-8 w-8 items-center justify-center rounded-md bg-white/15 font-bold">C</div>
            <div class="font-semibold leading-tight">Catalyst Works</div>
        </div>
        <nav class="space-y-5 px-3 py-2">
            @php($nav = [
                null => [['dashboard', 'Dashboard', ['admin','sales','service','accounts']], ['my', 'My jobs', ['technician','service','admin']]],
                'Sales' => [['customers.index', 'Customers', ['admin','sales','service','accounts']], ['enquiries.index', 'Enquiries', ['admin','sales']], ['quotations.index', 'Quotations', ['admin','sales','accounts']], ['orders.index', 'Orders', ['admin','sales','service','accounts']], ['invoices.index', 'Invoices', ['admin','sales','accounts']]],
                'Service' => [['complaints.index', 'Complaints', ['admin','sales','service','technician']], ['contracts.index', 'AMC contracts', ['admin','sales','service','accounts']], ['ppm.index', 'PPM visits', ['admin','service','technician']]],
                'Admin' => [['settings.index', 'Settings', ['admin']]],
            ])
            @foreach($nav as $group => $links)
                @php($links = array_filter($links, fn ($l) => $u->hasRole(...$l[2])))
                @if($links)
                <div>
                    @if($group)<div class="mb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-white/50">{{ $group }}</div>@endif
                    @foreach($links as [$route, $label])
                        @php($on = request()->routeIs(explode('.', $route)[0].'.*') || request()->routeIs($route))
                        <a href="{{ route($route) }}" class="navlink {{ $on ? 'navlink-on' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>
                @endif
            @endforeach
        </nav>
    </aside>
    <div x-show="nav" @click="nav=false" class="fixed inset-0 z-30 bg-black/40 lg:hidden" x-cloak></div>

    <div class="flex min-w-0 flex-1 flex-col">
        {{-- Top bar --}}
        <header class="sticky top-0 z-20 flex h-14 items-center gap-3 border-b border-slate-200 bg-white px-4">
            <button @click="nav=true" class="btn btn-sm lg:hidden" aria-label="Menu">☰</button>
            <form action="{{ route('search') }}" class="relative hidden flex-1 max-w-md sm:block">
                <input name="q" value="{{ request('q') }}" class="input py-1.5 pl-3" placeholder="Search customer, quotation no, complaint no…">
            </form>
            <div class="ml-auto flex items-center gap-2">
                <div x-data="{o:false}" class="relative">
                    <button @click="o=!o" class="btn btn-primary btn-sm">+ New</button>
                    <div x-show="o" @click.outside="o=false" x-cloak class="absolute right-0 mt-1 w-44 card py-1 text-[13px]">
                        @foreach([['complaints.create','Complaint',['admin','sales','service']],['enquiries.create','Enquiry',['admin','sales']],['quotations.create','Quotation',['admin','sales']],['customers.create','Customer',['admin','sales','service']]] as [$r,$l,$roles])
                            @if($u->hasRole(...$roles))<a href="{{ route($r) }}" class="block px-3 py-1.5 hover:bg-slate-50">{{ $l }}</a>@endif
                        @endforeach
                    </div>
                </div>
                <div x-data="{o:false}" class="relative">
                    <button @click="o=!o" class="flex items-center gap-2 rounded-md px-2 py-1 hover:bg-slate-50">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-100 text-xs font-semibold text-brand-800">{{ strtoupper(substr($u->name, 0, 1)) }}</span>
                        <span class="hidden text-[13px] sm:block">{{ $u->name }}</span>
                    </button>
                    <div x-show="o" @click.outside="o=false" x-cloak class="absolute right-0 mt-1 w-44 card py-1 text-[13px]">
                        <div class="px-3 py-1.5 text-xs text-slate-500">{{ \App\Models\User::ROLES[$u->role] ?? $u->role }}</div>
                        <form method="post" action="{{ route('logout') }}">@csrf<button class="block w-full px-3 py-1.5 text-left hover:bg-slate-50">Sign out</button></form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 lg:p-6">
            @if(session('ok'))<div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-2.5 text-[13px] text-green-800">{{ session('ok') }}</div>@endif
            @if($errors->any())<div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-2.5 text-[13px] text-red-800">{{ $errors->first() }}</div>@endif
            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>

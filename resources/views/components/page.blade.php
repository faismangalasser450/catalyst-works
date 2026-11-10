@props(['title', 'sub' => null, 'back' => null])
<div class="mb-4 flex flex-wrap items-start justify-between gap-3">
    <div>
        @if($back)<a href="{{ $back }}" class="text-xs text-slate-500 hover:text-brand-700">← Back</a>@endif
        <h1>{{ $title }}</h1>
        @if($sub)<p class="text-[13px] text-slate-500">{{ $sub }}</p>@endif
    </div>
    @if(isset($actions))<div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>@endif
</div>

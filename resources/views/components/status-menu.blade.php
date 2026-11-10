{{-- Coloured status pill; click it and pick the new status. Use `wire` (sprintf pattern) inside Livewire views, `action` for a plain POST. --}}
@props(['status', 'options', 'kind' => null, 'wire' => null, 'action' => null, 'links' => []])
<div {{ $attributes->merge(['class' => 'inline-block']) }}
     x-data="{ open: false, pos: '', toggle() { this.open = ! this.open; if (this.open) { const r = this.$refs.pill.getBoundingClientRect(); const up = window.innerHeight - r.bottom < 230; this.pos = 'left:' + Math.max(8, Math.min(r.left, window.innerWidth - 190)) + 'px;' + (up ? 'bottom:' + (window.innerHeight - r.top + 4) + 'px' : 'top:' + (r.bottom + 4) + 'px'); } } }"
     @keydown.escape.window="open = false" @scroll.window.capture="open = false" @resize.window="open = false">
    <button type="button" x-ref="pill" @click="toggle()" class="badge badge-btn {{ badge($status, $kind) }}" title="Change status" aria-haspopup="true">
        <span class="dot {{ status_dot($status, $kind) }}"></span>{{ $options[$status] ?? status_label($status) }}
        <svg class="ml-1 h-3 w-3 opacity-60" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 4.5 6 7.5 9 4.5"/></svg>
    </button>
    <div x-show="open" x-cloak @click.outside="open = false" :style="pos" class="status-menu">
        <div class="px-2 pb-1 pt-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400">Change status to</div>
        @foreach($options as $k => $l)
            @if($k === $status)
                <div class="status-item bg-slate-50 font-semibold"><span class="dot {{ status_dot($k, $kind) }}"></span>{{ $l }}<span class="ml-auto text-xs font-normal text-slate-400">current</span></div>
            @elseif(isset($links[$k]))
                <a href="{{ $links[$k] }}" class="status-item"><span class="dot {{ status_dot($k, $kind) }}"></span>{{ $l }}</a>
            @elseif($wire)
                <button type="button" wire:click="{{ sprintf($wire, $k) }}" @click="open = false" class="status-item"><span class="dot {{ status_dot($k, $kind) }}"></span>{{ $l }}</button>
            @else
                <form method="post" action="{{ $action }}">@csrf<input type="hidden" name="status" value="{{ $k }}"><button class="status-item"><span class="dot {{ status_dot($k, $kind) }}"></span>{{ $l }}</button></form>
            @endif
        @endforeach
    </div>
</div>

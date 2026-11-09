@props(['label', 'name', 'required' => false, 'hint' => null])
<div {{ $attributes }}>
    <label for="{{ $name }}">{{ $label }}@if($required)<span class="text-red-500"> *</span>@endif</label>
    {{ $slot }}
    @if($hint)<p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>@endif
    @error($name)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>

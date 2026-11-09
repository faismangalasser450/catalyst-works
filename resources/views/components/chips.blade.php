@props(['name', 'options', 'all' => 'All'])
@php($cur = request($name))
<div class="flex flex-wrap gap-1.5">
    <a href="{{ request()->fullUrlWithQuery([$name => null, 'page' => null]) }}" class="chip {{ $cur ? '' : 'chip-on' }}">{{ $all }}</a>
    @foreach($options as $value => $label)
        <a href="{{ request()->fullUrlWithQuery([$name => $value, 'page' => null]) }}" class="chip {{ $cur === (string) $value ? 'chip-on' : '' }}">{{ $label }}</a>
    @endforeach
</div>

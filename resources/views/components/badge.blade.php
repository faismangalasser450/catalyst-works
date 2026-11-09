@props(['status', 'label' => null])
<span class="badge {{ badge($status) }}">{{ $label ?? status_label($status) }}</span>

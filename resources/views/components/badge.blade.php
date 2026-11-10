@props(['status', 'label' => null, 'kind' => null])
<span class="badge {{ badge($status, $kind) }}"><span class="dot {{ status_dot($status, $kind) }}"></span>{{ $label ?? status_label($status) }}</span>

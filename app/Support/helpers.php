<?php

use Illuminate\Support\Carbon;

function money(float|int|string|null $n, bool $currency = false): string
{
    $s = number_format((float) $n, 2);

    return $currency ? 'AED '.$s : $s;
}

function dmy(Carbon|string|null $d): string
{
    if (! $d) {
        return '—';
    }

    return Carbon::parse($d)->format('d-m-Y');
}

function ago(?Carbon $d): string
{
    return $d ? $d->diffForHumans(null, true, true) : '—';
}

/**
 * Traffic-light tone of a status: red needs action, amber is waiting,
 * blue is being worked on, violet is a milestone, green is done, slate is inactive.
 */
function status_tone(string $status, ?string $kind = null): string
{
    if ($status === 'open' && $kind === 'enquiry') {
        return 'amber';
    }

    return match ($status) {
        'open', 'rejected', 'lost', 'missed', 'expired', 'overdue', 'cancelled', 'high' => 'red',
        'assigned', 'sent', 'unpaid', 'confirmed', 'due' => 'amber',
        'in_progress', 'part_paid', 'quoted', 'ordered', 'renewed' => 'blue',
        'visited', 'delivered' => 'violet',
        'approved', 'won', 'paid', 'done', 'closed', 'active', 'invoiced' => 'green',
        default => 'slate',
    };
}

function badge(string $status, ?string $kind = null): string
{
    return match (status_tone($status, $kind)) {
        'red' => 'bg-red-100 text-red-800 ring-red-600/25',
        'amber' => 'bg-amber-100 text-amber-900 ring-amber-600/30',
        'blue' => 'bg-blue-100 text-blue-800 ring-blue-600/25',
        'violet' => 'bg-violet-100 text-violet-800 ring-violet-600/25',
        'green' => 'bg-green-100 text-green-800 ring-green-600/25',
        default => 'bg-slate-100 text-slate-700 ring-slate-500/25',
    };
}

function status_dot(string $status, ?string $kind = null): string
{
    return match (status_tone($status, $kind)) {
        'red' => 'bg-red-500',
        'amber' => 'bg-amber-500',
        'blue' => 'bg-blue-500',
        'violet' => 'bg-violet-500',
        'green' => 'bg-green-500',
        default => 'bg-slate-400',
    };
}

function status_solid(string $status, ?string $kind = null): string
{
    return match (status_tone($status, $kind)) {
        'red' => 'border-red-600 bg-red-600 text-white hover:bg-red-700',
        'amber' => 'border-amber-500 bg-amber-500 text-white hover:bg-amber-600',
        'blue' => 'border-blue-600 bg-blue-600 text-white hover:bg-blue-700',
        'violet' => 'border-violet-600 bg-violet-600 text-white hover:bg-violet-700',
        'green' => 'border-green-600 bg-green-600 text-white hover:bg-green-700',
        default => 'border-slate-500 bg-slate-500 text-white hover:bg-slate-600',
    };
}

function status_label(string $status): string
{
    return ucfirst(str_replace('_', ' ', $status));
}

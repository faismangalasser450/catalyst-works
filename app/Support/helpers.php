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

function ago(Carbon|null $d): string
{
    return $d ? $d->diffForHumans(null, true, true) : '—';
}

function badge(string $status): string
{
    return match ($status) {
        'open', 'draft', 'unpaid', 'due', 'confirmed' => 'bg-slate-100 text-slate-700',
        'sent', 'assigned', 'in_progress', 'part_paid', 'quoted' => 'bg-blue-50 text-blue-700',
        'approved', 'won', 'paid', 'done', 'closed', 'delivered', 'active', 'invoiced', 'ordered' => 'bg-green-50 text-green-700',
        'visited' => 'bg-amber-50 text-amber-700',
        'rejected', 'lost', 'missed', 'expired', 'cancelled', 'high' => 'bg-red-50 text-red-700',
        'renewed' => 'bg-purple-50 text-purple-700',
        default => 'bg-slate-100 text-slate-700',
    };
}

function status_label(string $status): string
{
    return ucfirst(str_replace('_', ' ', $status));
}

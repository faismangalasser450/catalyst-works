<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    /** URL prefix => roles allowed (admin always allowed). */
    public const AREAS = [
        'customers' => ['sales', 'service', 'accounts'],
        'enquiries' => ['sales'],
        'quotations' => ['sales', 'service', 'accounts'],
        'orders' => ['sales', 'accounts'],
        'invoices' => ['sales', 'accounts'],
        'complaints' => ['sales', 'service', 'technician'],
        'contracts' => ['sales', 'service', 'accounts'],
        'ppm' => ['service', 'technician'],
        'settings' => [],
    ];

    /** Technicians may only read within their areas, except their own My jobs page. */
    public const TECH_WRITE_OK = ['my', 'logout', 'livewire'];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $area = $request->segment(1);
        if (! $user || $user->isAdmin() || $area === null || ! array_key_exists($area, self::AREAS)) {
            return $next($request);
        }
        abort_unless(in_array($user->role, self::AREAS[$area]), 403, 'Your role does not have access to this section.');
        if ($user->isTechnician() && ! $request->isMethodSafe()) {
            abort(403, 'Technicians update jobs from My jobs.');
        }
        if ($user->role === 'accounts' && ! $request->isMethodSafe() && in_array($area, ['customers', 'quotations', 'contracts'])) {
            abort(403, 'Accounts can view this but not change it.');
        }

        return $next($request);
    }
}

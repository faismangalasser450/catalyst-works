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

    /** Can this user see (or, with $write, change) records in an area? */
    public static function allows(?\App\Models\User $user, string $area, bool $write = false): bool
    {
        if (! $user || ! array_key_exists($area, self::AREAS)) {
            return (bool) $user;
        }
        if ($user->isAdmin()) {
            return true;
        }
        if (! in_array($user->role, self::AREAS[$area])) {
            return false;
        }
        if (! $write) {
            return true;
        }
        if ($user->isTechnician()) {
            return false;
        }

        return ! ($user->role === 'accounts' && in_array($area, ['customers', 'quotations', 'contracts']));
    }

    /** For Livewire actions: 403 unless the user may change this area. */
    public static function write(string $area): void
    {
        abort_unless(self::allows(auth()->user(), $area, true), 403, 'Your role cannot change this.');
    }

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $area = $request->segment(1);
        if (! $user || $area === null || ! array_key_exists($area, self::AREAS)) {
            return $next($request);
        }
        abort_unless(self::allows($user, $area), 403, 'Your role does not have access to this section.');
        abort_unless($request->isMethodSafe() || self::allows($user, $area, true), 403, 'Your role can view this but not change it.');

        return $next($request);
    }
}

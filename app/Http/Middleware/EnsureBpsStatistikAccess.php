<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for the four BPS Statistik dashboards (/bps/statistik*): BPS Batam and
 * BPS Provinsi.
 *
 * BPS Provinsi deliberately has no `is_bps`, so every other BPS gate (`is_bps`:
 * the rest of /bps, MONALISA BPS, Buku Tamu settings, /develop) treats it as a
 * regular user. This middleware is the one exception, and it belongs on the
 * statistik routes only.
 */
class EnsureBpsStatistikAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->canViewBpsStatistik()) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}

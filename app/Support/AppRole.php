<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Which route surface this container serves.
 *
 * One codebase, two products: DataKita itself and the developer portal that
 * fronts third-party apps. They share the User model, the roles, the session
 * config and APP_KEY — see the developer-portal section of CLAUDE.md for why
 * splitting the repo was rejected — but they must not share a route table. A
 * portal container has no business publishing /bps or /superadmin.
 *
 * Read from APP_ROLE via config('app.role'), and acted on in
 * {@see \App\Providers\RouteServiceProvider::boot()}.
 */
final class AppRole
{
    /** DataKita only. The default, and what production runs. */
    public const DATAKITA = 'datakita';

    /** Both surfaces. datakitadev only — a dev convenience, never production. */
    public const ALL = 'all';

    /** Developer portal and its proxy only (apps.angkabatam.id). */
    public const DEVPORTAL = 'devportal';

    private const KNOWN = [self::DATAKITA, self::ALL, self::DEVPORTAL];

    /**
     * The configured role, normalised.
     */
    public static function current(): string
    {
        $role = strtolower(trim((string) config('app.role')));

        // Unset (or empty, which is how Docker passes a blank var) is the
        // documented default: a plain DataKita deployment.
        if ($role === '') {
            return self::DATAKITA;
        }

        // A typo must not quietly fall back to DATAKITA. That failure mode
        // publishes /bps and /superadmin on a host meant to serve the portal
        // alone, and nothing downstream would notice.
        if (! in_array($role, self::KNOWN, true)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid APP_ROLE [%s]. Expected one of: %s — or leave it unset for %s.',
                $role,
                implode(', ', self::KNOWN),
                self::DATAKITA,
            ));
        }

        return $role;
    }

    /**
     * Whether routes/web.php — DataKita proper — is served here.
     */
    public static function servesDatakita(): bool
    {
        return in_array(self::current(), [self::DATAKITA, self::ALL], true);
    }

    /**
     * Whether routes/devportal.php — the /develop portal — is served here.
     *
     * Anything that links to a `develop.*` route must check this first, or
     * route() throws once the portal is not part of this container's surface.
     */
    public static function servesDevPortal(): bool
    {
        return in_array(self::current(), [self::DEVPORTAL, self::ALL], true);
    }
}

<?php

namespace App\Http\Controllers\Develop\Concerns;

use App\Models\User;
use Illuminate\Http\Response;

/**
 * The pieces the two gate implementations must answer identically.
 *
 * There are two of them because the edge gate (AuthzController, Traefik
 * forwardAuth) and the in-app gate (ProxyController) run in different places.
 * They must not drift: if one of them decided identity differently from the
 * other, whichever is deployed would quietly grant different access.
 */
trait GateResponses
{
    /**
     * The identity a dev app is told about the current visitor.
     *
     * Every configured header is always present, empty for an anonymous
     * visitor. Omitting them for anonymous would let a client supply its own
     * X-Datakita-User-Id and have it forwarded untouched.
     *
     * @return array<string, string> header name => value
     */
    protected function identityHeaderValues(?User $user): array
    {
        $headers = config('devapps.identity_headers', []);

        $values = [
            $headers['id'] ?? 'X-Datakita-User-Id'      => '',
            $headers['name'] ?? 'X-Datakita-User-Name'  => '',
            $headers['email'] ?? 'X-Datakita-User-Email' => '',
            $headers['role'] ?? 'X-Datakita-User-Role'  => '',
        ];

        if (! $user) {
            return $values;
        }

        return [
            $headers['id'] ?? 'X-Datakita-User-Id'       => (string) $user->id,
            // Names can carry non-ASCII; header values must not.
            $headers['name'] ?? 'X-Datakita-User-Name'   => $this->headerSafe($user->name),
            $headers['email'] ?? 'X-Datakita-User-Email' => (string) $user->email,
            $headers['role'] ?? 'X-Datakita-User-Role'   => (string) $user->role,
        ];
    }

    /**
     * HTTP header values must be ISO-8859-1; transliterate anything else so a
     * user with an accented name doesn't break the whole response.
     */
    protected function headerSafe(?string $value): string
    {
        $value = (string) $value;

        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT', $value);

        return preg_replace('/[^\x20-\x7E]/', '', $ascii === false ? $value : $ascii) ?? '';
    }

    /**
     * A small, self-contained error page. It renders inside whatever the
     * browser was loading, so it stays dependency-free.
     */
    protected function gatePage(string $title, string $message, int $status): Response
    {
        $safeTitle = e($title);
        $safe      = e($message);
        $home      = e(url('/'));

        $html = <<<HTML
        <!doctype html>
        <html lang="id"><head><meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{$safeTitle}</title>
        <style>
          body{font-family:system-ui,-apple-system,"Segoe UI",sans-serif;background:#f8fafc;color:#0f172a;
               display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:1.5rem}
          .card{background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:2rem;max-width:26rem;
                box-shadow:0 1px 3px rgba(0,0,0,.08);text-align:center}
          h1{font-size:1.125rem;margin:0 0 .5rem}
          p{color:#475569;font-size:.9375rem;line-height:1.5;margin:0 0 1.25rem}
          a{display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:.5rem 1rem;
            border-radius:.5rem;font-size:.875rem;font-weight:500}
          @media (prefers-color-scheme:dark){
            body{background:#0f172a;color:#f1f5f9}
            .card{background:#1e293b;border-color:#334155}
            p{color:#94a3b8}
          }
        </style></head>
        <body><div class="card">
          <h1>{$safeTitle}</h1>
          <p>{$safe}</p>
          <a href="{$home}">Kembali ke DataKita</a>
        </div></body></html>
        HTML;

        return response($html, $status)->header('Content-Type', 'text/html; charset=utf-8');
    }
}

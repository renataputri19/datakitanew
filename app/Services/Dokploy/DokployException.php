<?php

namespace App\Services\Dokploy;

use RuntimeException;
use Throwable;

/**
 * Thrown when the Dokploy API is unreachable, rejects our credentials, or
 * answers with an error. Carries the endpoint and the raw response body so
 * the portal can show the developer something actionable instead of
 * "something went wrong".
 */
class DokployException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?string $endpoint = null,
        public readonly ?int $status = null,
        public readonly ?string $body = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * A short, safe line for a flash message — no credentials, no stack.
     */
    public function summary(): string
    {
        $parts = array_filter([
            $this->endpoint ? "endpoint {$this->endpoint}" : null,
            $this->status ? "HTTP {$this->status}" : null,
        ]);

        return $parts
            ? $this->getMessage() . ' (' . implode(', ', $parts) . ')'
            : $this->getMessage();
    }
}

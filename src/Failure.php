<?php

declare(strict_types=1);

namespace Prefixcache;

use RuntimeException;

/**
 * A failure that ends the command: status 1 for the script, 2 for the command line.
 */
final class Failure extends RuntimeException
{
    public function __construct(private readonly int $status, string $message)
    {
        parent::__construct($message);
    }

    public function status(): int
    {
        return $this->status;
    }
}

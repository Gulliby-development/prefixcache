<?php

declare(strict_types=1);

namespace Prefixcache;

final class Registry
{
    public const VERSION = '0.1.0';
    public const DEFAULT_BLOCK = 4;
    public const MAX_BLOCK = 16;
    public const DEFAULT_CAP = 8;
    public const MAX_CAP = 32;
    public const MAX_LINES = 40;
    public const MAX_TOKENS = 48;

    public const USAGE = "usage: prefixcache trace [--block N] [--cap N]\n"
        . "       prefixcache cache [--block N] [--cap N]\n"
        . "       prefixcache stats [--block N] [--cap N]\n"
        . "       prefixcache check [--block N] [--cap N]\n";

    /**
     * One entry per command: its word and the method of Prefixcache\Commands that runs it.
     *
     * @var list<array{name: string, handler: string}>
     */
    public const COMMANDS = [
        ['name' => 'trace', 'handler' => 'trace'],
        ['name' => 'cache', 'handler' => 'cache'],
        ['name' => 'stats', 'handler' => 'stats'],
        ['name' => 'check', 'handler' => 'check'],
    ];
}

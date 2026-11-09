<?php

declare(strict_types=1);

namespace Prefixcache;

/**
 * Operational command handlers.
 */
final class Commands
{
    /**
     * @param array{block: int, cap: int} $options
     * @return array{status: int, out: string, err: string}
     */
    public static function trace(array $options, string $input): array
    {
        $summary = Runner::run($options, $input);

        return ['status' => 0, 'out' => Report::trace($summary), 'err' => ''];
    }

    /**
     * @param array{block: int, cap: int} $options
     * @return array{status: int, out: string, err: string}
     */
    public static function cache(array $options, string $input): array
    {
        $summary = Runner::run($options, $input);

        return ['status' => 0, 'out' => Report::cache($summary), 'err' => ''];
    }

    /**
     * @param array{block: int, cap: int} $options
     * @return array{status: int, out: string, err: string}
     */
    public static function stats(array $options, string $input): array
    {
        $summary = Runner::run($options, $input);

        return ['status' => 0, 'out' => Report::stats($summary), 'err' => ''];
    }

    /**
     * @param array{block: int, cap: int} $options
     * @return array{status: int, out: string, err: string}
     */
    public static function check(array $options, string $input): array
    {
        $summary = Runner::run($options, $input);

        return ['status' => 0, 'out' => Report::check($summary), 'err' => ''];
    }
}

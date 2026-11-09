<?php

declare(strict_types=1);

namespace Prefixcache;

/**
 * @phpstan-type Block array{depth: int, tokens: list<string>, touch: int}
 * @phpstan-type Event array{kind: 'req', id: string, tokens: int, hit: int, new: int, evicted: int}|array{kind: 'flush', dropped: int}
 * @phpstan-type Summary array{commands: int, requests: int, tokens: int, hit: int, evicted: int, cap: int, blocks: list<Block>, events: list<Event>}
 */
final class Report
{
    /**
     * @param Summary $summary
     */
    public static function check(array $summary): string
    {
        $commands = $summary['commands'];
        $noun = $commands === 1 ? 'command' : 'commands';

        return 'ok ' . $commands . ' ' . $noun . "\n";
    }

    /**
     * @param Summary $summary
     */
    public static function trace(array $summary): string
    {
        $lines = '';
        foreach ($summary['events'] as $event) {
            if ($event['kind'] === 'flush') {
                $lines .= 'flush dropped ' . $event['dropped'] . "\n";
                continue;
            }

            $lines .= 'req ' . $event['id']
                . ' tokens ' . $event['tokens']
                . ' hit ' . $event['hit']
                . ' new ' . $event['new']
                . ' evicted ' . $event['evicted'] . "\n";
        }

        return $lines;
    }

    /**
     * @param Summary $summary
     */
    public static function cache(array $summary): string
    {
        $blocks = $summary['blocks'];
        usort($blocks, static fn (array $left, array $right): int => $right['touch'] <=> $left['touch']);
        $lines = 'blocks ' . count($blocks) . ' of ' . $summary['cap'] . "\n";
        foreach ($blocks as $block) {
            $lines .= 'depth ' . $block['depth'] . ' tokens ' . implode(',', $block['tokens']) . "\n";
        }

        return $lines;
    }

    /**
     * @param Summary $summary
     */
    public static function stats(array $summary): string
    {
        $tokens = $summary['tokens'];
        $rate = $tokens === 0 ? 0 : intdiv($summary['hit'] * 100, $tokens);
        $values = [
            'requests' => $summary['requests'],
            'tokens' => $tokens,
            'hit' => $summary['hit'],
            'computed' => $tokens - $summary['hit'],
            'rate' => $rate,
            'blocks' => count($summary['blocks']),
            'evicted' => $summary['evicted'],
        ];
        $lines = '';
        foreach ($values as $name => $value) {
            $lines .= $name . ' ' . $value . "\n";
        }

        return $lines;
    }
}

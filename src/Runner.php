<?php

declare(strict_types=1);

namespace Prefixcache;

/**
 * Executes validated script commands against a fresh cache and collects replay results.
 *
 * @phpstan-type Event array{kind: 'req', id: string, tokens: int, hit: int, new: int, evicted: int}|array{kind: 'flush', dropped: int}
 * @phpstan-type Snapshot array{depth: int, tokens: list<string>, touch: int}
 * @phpstan-type Summary array{commands: int, requests: int, tokens: int, hit: int, evicted: int, cap: int, blocks: list<Snapshot>, events: list<Event>}
 */
final class Runner
{
    /**
     * @param array{block: int, cap: int} $options
     * @return Summary
     */
    public static function run(array $options, string $input): array
    {
        $cache = new Cache($options['block'], $options['cap']);
        $used = [];
        $events = [];
        $commands = 0;
        $requests = 0;
        $tokensTotal = 0;
        $hitTotal = 0;
        $evictedTotal = 0;

        foreach (Script::lines($input) as $index => $line) {
            $command = Script::command($line, $index + 1, $used);
            if ($command === null) {
                continue;
            }

            $commands++;
            if ($command['kind'] === 'flush') {
                $events[] = ['kind' => 'flush', 'dropped' => $cache->flush()];
                continue;
            }

            $id = $command['id'];
            $requestTokens = $command['tokens'];
            $result = $cache->serve($requestTokens);
            $requestTokenCount = count($requestTokens);
            $requestHitTokens = $result['hit'] * $options['block'];
            $events[] = [
                'kind' => 'req',
                'id' => $id,
                'tokens' => $requestTokenCount,
                'hit' => $requestHitTokens,
                'new' => $result['new'],
                'evicted' => $result['evicted'],
            ];
            $used[$id] = true;
            $requests++;
            $tokensTotal += $requestTokenCount;
            $hitTotal += $requestHitTokens;
            $evictedTotal += $result['evicted'];
        }

        return [
            'commands' => $commands,
            'requests' => $requests,
            'tokens' => $tokensTotal,
            'hit' => $hitTotal,
            'evicted' => $evictedTotal,
            'cap' => $options['cap'],
            'blocks' => $cache->snapshot(),
            'events' => $events,
        ];
    }
}

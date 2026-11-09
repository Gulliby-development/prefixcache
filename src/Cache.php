<?php

declare(strict_types=1);

namespace Prefixcache;

/**
 * @phpstan-type Entry array{depth: int, tokens: list<string>, touch: int, parent: string|null}
 * @phpstan-type Snapshot array{depth: int, tokens: list<string>, touch: int}
 */
final class Cache
{
    private int $clock = 0;

    /** @var array<string, Entry> */
    private array $entries = [];

    public function __construct(private readonly int $block, private readonly int $cap)
    {
    }

    /**
     * @param list<string> $tokens
     * @return array{hit: int, new: int, evicted: int}
     */
    public function serve(array $tokens): array
    {
        $full = intdiv(count($tokens), $this->block);
        $keys = [];
        $parents = [];
        $parts = [];
        for ($depth = 1; $depth <= $full; $depth++) {
            $parts = array_slice($tokens, 0, $depth * $this->block);
            $keys[$depth] = 'prefix:' . implode(',', $parts);
            $parents[$depth] = $depth === 1 ? null : $keys[$depth - 1];
        }

        $hit = 0;
        $protected = [];
        for ($depth = 1; $depth <= $full; $depth++) {
            $key = $keys[$depth];
            if (!isset($this->entries[$key])) {
                break;
            }
            $hit++;
            $protected[$key] = true;
            $this->touch($key);
        }

        $new = 0;
        $evicted = 0;
        for ($depth = $hit + 1; $depth <= $full; $depth++) {
            if (count($this->entries) >= $this->cap) {
                $victim = $this->oldestEligibleLeaf($protected);
                if ($victim === null) {
                    break;
                }
                unset($this->entries[$victim]);
                $evicted++;
            }

            $key = $keys[$depth];
            $blockTokens = array_slice($tokens, ($depth - 1) * $this->block, $this->block);
            $this->entries[$key] = [
                'depth' => $depth,
                'tokens' => $blockTokens,
                'touch' => 0,
                'parent' => $parents[$depth],
            ];
            $protected[$key] = true;
            $this->touch($key);
            $new++;
        }

        return ['hit' => $hit, 'new' => $new, 'evicted' => $evicted];
    }

    public function flush(): int
    {
        $count = count($this->entries);
        $this->entries = [];

        return $count;
    }

    /**
     * @return list<Snapshot>
     */
    public function snapshot(): array
    {
        $snapshot = [];
        foreach ($this->entries as $entry) {
            $snapshot[] = [
                'depth' => $entry['depth'],
                'tokens' => $entry['tokens'],
                'touch' => $entry['touch'],
            ];
        }

        return $snapshot;
    }

    private function touch(string $key): void
    {
        $this->clock++;
        $this->entries[$key]['touch'] = $this->clock;
    }

    /**
     * @param array<string, true> $protected
     */
    private function oldestEligibleLeaf(array $protected): ?string
    {
        $oldestKey = null;
        $oldestTouch = PHP_INT_MAX;
        foreach ($this->entries as $key => $entry) {
            if (isset($protected[$key]) || $this->hasChild($key)) {
                continue;
            }
            if ($entry['touch'] < $oldestTouch) {
                $oldestTouch = $entry['touch'];
                $oldestKey = $key;
            }
        }

        return $oldestKey;
    }

    private function hasChild(string $key): bool
    {
        foreach ($this->entries as $entry) {
            if ($entry['parent'] === $key) {
                return true;
            }
        }

        return false;
    }
}

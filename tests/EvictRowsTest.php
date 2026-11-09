<?php

declare(strict_types=1);

namespace Prefixcache\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EvictRowsTest extends TestCase
{
    /**
     * @param list<string> $args
     */
    #[DataProvider('rows')]
    public function testRow(array $args, string $input, int $status, string $out, string $err): void
    {
        Harness::assertReply($args, $input, $status, $out, $err);
    }

    /**
     * @return list<array{list<string>, string, int, string, string}>
     */
    public static function rows(): array
    {
        return [
            [["trace", "--block", "2", "--cap", "3"], "req a 1 2 3 4 5 6\nreq b 1 2 7 8\nreq c 1 2 3 4 9 9\nreq d 1 2 7 8 5 5\n", 0, "req a tokens 6 hit 0 new 3 evicted 0\nreq b tokens 4 hit 2 new 1 evicted 1\nreq c tokens 6 hit 4 new 1 evicted 1\nreq d tokens 6 hit 2 new 2 evicted 2\n", ""],
            [["trace", "--block", "2", "--cap", "3"], "req a 1 2 3 4\nreq b 5 6 7 8\nreq c 9 9 9 9\nreq d 1 2 3 4\n", 0, "req a tokens 4 hit 0 new 2 evicted 0\nreq b tokens 4 hit 0 new 2 evicted 1\nreq c tokens 4 hit 0 new 2 evicted 2\nreq d tokens 4 hit 0 new 2 evicted 2\n", ""],
            [["trace", "--block", "2", "--cap", "2"], "req a 1 2 3 4\nreq b 5 6 7 8\nreq c 9 9 9 9\nreq d 1 2 3 4\n", 0, "req a tokens 4 hit 0 new 2 evicted 0\nreq b tokens 4 hit 0 new 2 evicted 2\nreq c tokens 4 hit 0 new 2 evicted 2\nreq d tokens 4 hit 0 new 2 evicted 2\n", ""],
            [["trace", "--block", "2", "--cap", "2"], "req a 1 2 3 4 5 6 7 8\nreq b 1 2 3 4 5 6 7 8\n", 0, "req a tokens 8 hit 0 new 2 evicted 0\nreq b tokens 8 hit 4 new 0 evicted 0\n", ""],
            [["trace", "--block", "2", "--cap", "2"], "req a 1 2 3 4 5 6\n", 0, "req a tokens 6 hit 0 new 2 evicted 0\n", ""],
            [["trace", "--block", "2", "--cap", "2"], "req a 1 2\nreq b 3 4\nreq c 5 6\nreq d 3 4 7 8\n", 0, "req a tokens 2 hit 0 new 1 evicted 0\nreq b tokens 2 hit 0 new 1 evicted 0\nreq c tokens 2 hit 0 new 1 evicted 1\nreq d tokens 4 hit 2 new 1 evicted 1\n", ""],
            [["trace", "--block", "2", "--cap", "1"], "req a 1 2 3 4\nreq b 5 6\nreq c 5 6\n", 0, "req a tokens 4 hit 0 new 1 evicted 0\nreq b tokens 2 hit 0 new 1 evicted 1\nreq c tokens 2 hit 2 new 0 evicted 0\n", ""],
            [["trace", "--block", "2", "--cap", "1"], "req a 1 2\nreq b 1 2\nreq c 3 4\nreq d 1 2\n", 0, "req a tokens 2 hit 0 new 1 evicted 0\nreq b tokens 2 hit 2 new 0 evicted 0\nreq c tokens 2 hit 0 new 1 evicted 1\nreq d tokens 2 hit 0 new 1 evicted 1\n", ""],
            [["stats", "--block", "2", "--cap", "2"], "req a 1 2 3 4\nreq b 5 6 7 8\nreq c 9 9 9 9\nreq d 1 2 3 4\n", 0, "requests 4\ntokens 16\nhit 0\ncomputed 16\nrate 0\nblocks 2\nevicted 6\n", ""],
        ];
    }
}

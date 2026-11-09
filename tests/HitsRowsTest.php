<?php

declare(strict_types=1);

namespace Prefixcache\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HitsRowsTest extends TestCase
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
            [["trace"], "req a 1 2 3 4 5 6 7 8\nreq b 1 2 3 4 9 9 9 9\nreq c 1 2 3 4 5 6 7 8 9\n", 0, "req a tokens 8 hit 0 new 2 evicted 0\nreq b tokens 8 hit 4 new 1 evicted 0\nreq c tokens 9 hit 8 new 0 evicted 0\n", ""],
            [["trace", "--block", "2"], "req a 1 2 3 4 5 6 7 8\nreq b 1 2 3 4 9 9 9 9\nreq c 1 2 3 4 5 6 7 8 9\n", 0, "req a tokens 8 hit 0 new 4 evicted 0\nreq b tokens 8 hit 4 new 2 evicted 0\nreq c tokens 9 hit 8 new 0 evicted 0\n", ""],
            [["trace"], "req a 1 2 3\nreq b 1 2 3 4\nreq c 1 2 3 4 5\n", 0, "req a tokens 3 hit 0 new 0 evicted 0\nreq b tokens 4 hit 0 new 1 evicted 0\nreq c tokens 5 hit 4 new 0 evicted 0\n", ""],
            [["trace", "--block", "2"], "req a 1 2 3\nreq b 1 2 3 4\nreq c 1 2 3 4 5\n", 0, "req a tokens 3 hit 0 new 1 evicted 0\nreq b tokens 4 hit 2 new 1 evicted 0\nreq c tokens 5 hit 4 new 0 evicted 0\n", ""],
            [["trace", "--block", "2"], "req a 1 2\nreq b 1 2\nreq c 1 2 3\n", 0, "req a tokens 2 hit 0 new 1 evicted 0\nreq b tokens 2 hit 2 new 0 evicted 0\nreq c tokens 3 hit 2 new 0 evicted 0\n", ""],
            [["trace", "--block", "2"], "req a 1 2 3 4\nreq b 3 4 1 2\n", 0, "req a tokens 4 hit 0 new 2 evicted 0\nreq b tokens 4 hit 0 new 2 evicted 0\n", ""],
            [["trace", "--block", "2"], "req a 1 2 3 4\nreq b 1 2 3 5\n", 0, "req a tokens 4 hit 0 new 2 evicted 0\nreq b tokens 4 hit 2 new 1 evicted 0\n", ""],
            [["trace", "--block", "2"], "req a 1 2 3 4\nreq b 1 3 3 4\n", 0, "req a tokens 4 hit 0 new 2 evicted 0\nreq b tokens 4 hit 0 new 2 evicted 0\n", ""],
            [["trace", "--block", "2"], "req a 1 2 3 4 5 6\nreq b 1 2 3 4\n", 0, "req a tokens 6 hit 0 new 3 evicted 0\nreq b tokens 4 hit 4 new 0 evicted 0\n", ""],
            [["trace", "--block", "2"], "req a 1 2 3 4\nreq b 1 2 3 4 5 6 7 8 9\n", 0, "req a tokens 4 hit 0 new 2 evicted 0\nreq b tokens 9 hit 4 new 2 evicted 0\n", ""],
            [["stats"], "req a 1 2 3 4 5 6 7 8\nreq b 1 2 3 4 9 9 9 9\nreq c 1 2 3 4 5 6 7 8 9\n", 0, "requests 3\ntokens 25\nhit 12\ncomputed 13\nrate 48\nblocks 3\nevicted 0\n", ""],
        ];
    }
}

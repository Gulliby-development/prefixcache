<?php

declare(strict_types=1);

namespace Prefixcache\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RequestRowsTest extends TestCase
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
            [["trace", "--block", "2"], "req a 1 2 3 4\n", 0, "req a tokens 4 hit 0 new 2 evicted 0\n", ""],
            [["trace"], "req a 1 2 3 4 5 6 7 8\n", 0, "req a tokens 8 hit 0 new 2 evicted 0\n", ""],
            [["trace"], "req a 1 2 3\n", 0, "req a tokens 3 hit 0 new 0 evicted 0\n", ""],
            [["trace"], "req a 1\n", 0, "req a tokens 1 hit 0 new 0 evicted 0\n", ""],
            [["trace"], "req a 1 2 3 4\n", 0, "req a tokens 4 hit 0 new 1 evicted 0\n", ""],
            [["trace", "--block", "2"], "req a 1 2 3\n", 0, "req a tokens 3 hit 0 new 1 evicted 0\n", ""],
            [["trace", "--block", "1"], "req a 5 6 7\n", 0, "req a tokens 3 hit 0 new 3 evicted 0\n", ""],
            [["trace", "--block", "3"], "req a 1 2 3 4 5 6 7\n", 0, "req a tokens 7 hit 0 new 2 evicted 0\n", ""],
            [["trace", "--block", "16"], "req a 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19 20 21 22 23 24 25 26 27 28 29 30 31 32\n", 0, "req a tokens 32 hit 0 new 2 evicted 0\n", ""],
            [["trace", "--block", "16"], "req a 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15\n", 0, "req a tokens 15 hit 0 new 0 evicted 0\n", ""],
            [["trace"], "req a 0 0 0 0\nreq b 0 0 0 0 0 0\nreq c 0 0\n", 0, "req a tokens 4 hit 0 new 1 evicted 0\nreq b tokens 6 hit 4 new 0 evicted 0\nreq c tokens 2 hit 0 new 0 evicted 0\n", ""],
            [["trace"], "req a 0\nreq b 0 0 0 0\nreq c 0 0 0 0\n", 0, "req a tokens 1 hit 0 new 0 evicted 0\nreq b tokens 4 hit 0 new 1 evicted 0\nreq c tokens 4 hit 4 new 0 evicted 0\n", ""],
            [["trace"], "req a 9999 9999 9999 9999\n", 0, "req a tokens 4 hit 0 new 1 evicted 0\n", ""],
            [["trace"], "req a 5 5 5 5\nreq b 5 5 5 5 5 5 5 5\n", 0, "req a tokens 4 hit 0 new 1 evicted 0\nreq b tokens 8 hit 4 new 1 evicted 0\n", ""],
        ];
    }
}

<?php

declare(strict_types=1);

namespace Prefixcache\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TreeRowsTest extends TestCase
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
            [["trace", "--block", "2"], "req a 1 2 3 4 5 6\nreq b 1 2 7 8\nreq c 1 2 3 4 9 9\nreq d 1 2 7 8 5 5\n", 0, "req a tokens 6 hit 0 new 3 evicted 0\nreq b tokens 4 hit 2 new 1 evicted 0\nreq c tokens 6 hit 4 new 1 evicted 0\nreq d tokens 6 hit 4 new 1 evicted 0\n", ""],
            [["trace", "--block", "2"], "req a 1 2\nreq b 1 2 3 4\nreq c 1 2 3 4 5 6\n", 0, "req a tokens 2 hit 0 new 1 evicted 0\nreq b tokens 4 hit 2 new 1 evicted 0\nreq c tokens 6 hit 4 new 1 evicted 0\n", ""],
            [["stats", "--block", "2"], "req a 1 2 3 4 5 6\nreq b 1 2 7 8\nreq c 1 2 3 4 9 9\nreq d 1 2 7 8 5 5\n", 0, "requests 4\ntokens 22\nhit 10\ncomputed 12\nrate 45\nblocks 6\nevicted 0\n", ""],
            [["trace", "--block", "1", "--cap", "3"], "req a 1 2 3\nreq b 1 2 3\nreq c 1 2 4\n", 0, "req a tokens 3 hit 0 new 3 evicted 0\nreq b tokens 3 hit 3 new 0 evicted 0\nreq c tokens 3 hit 2 new 1 evicted 1\n", ""],
            [["cache", "--block", "2"], "req a 1 2 3 4 5 6\nreq b 1 2 7 8\nreq c 1 2 3 4 9 9\nreq d 1 2 7 8 5 5\n", 0, "blocks 6 of 8\ndepth 3 tokens 5,5\ndepth 2 tokens 7,8\ndepth 1 tokens 1,2\ndepth 3 tokens 9,9\ndepth 2 tokens 3,4\ndepth 3 tokens 5,6\n", ""],
            [["cache", "--block", "2"], "req a 1 2 3 4\nreq b 5 6\nreq c 1 2 3 4\n", 0, "blocks 3 of 8\ndepth 2 tokens 3,4\ndepth 1 tokens 1,2\ndepth 1 tokens 5,6\n", ""],
        ];
    }
}

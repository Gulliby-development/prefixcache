<?php

declare(strict_types=1);

namespace Prefixcache\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StatsRowsTest extends TestCase
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
            [["stats"], "", 0, "requests 0\ntokens 0\nhit 0\ncomputed 0\nrate 0\nblocks 0\nevicted 0\n", ""],
            [["stats"], "# nothing\n", 0, "requests 0\ntokens 0\nhit 0\ncomputed 0\nrate 0\nblocks 0\nevicted 0\n", ""],
            [["stats"], "req a 1 2 3 4 5 6 7 8\nreq b 1 2 3 4 9 9 9 9\nreq c 1 2 3 4 5 6 7 8 9\n", 0, "requests 3\ntokens 25\nhit 12\ncomputed 13\nrate 48\nblocks 3\nevicted 0\n", ""],
            [["stats"], "req a 1 2 3\nreq b 1 2 3 4\nreq c 1 2 3 4 5\n", 0, "requests 3\ntokens 12\nhit 4\ncomputed 8\nrate 33\nblocks 1\nevicted 0\n", ""],
            [["stats", "--block", "2"], "req a 1 2 3\nreq b 1 2 3 4\nreq c 1 2 3 4 5\n", 0, "requests 3\ntokens 12\nhit 6\ncomputed 6\nrate 50\nblocks 2\nevicted 0\n", ""],
            [["stats", "--block", "2"], "req a 1 2\nreq b 1 2\nreq c 1 2 3\n", 0, "requests 3\ntokens 7\nhit 4\ncomputed 3\nrate 57\nblocks 1\nevicted 0\n", ""],
            [["stats", "--block", "2"], "req a 1 2 3 4 5 6\nreq b 1 2 7 8\nreq c 1 2 3 4 9 9\nreq d 1 2 7 8 5 5\n", 0, "requests 4\ntokens 22\nhit 10\ncomputed 12\nrate 45\nblocks 6\nevicted 0\n", ""],
            [["stats"], "req a 1 2 3 4\nflush\nreq b 1 2 3 4\nflush\nflush\n", 0, "requests 2\ntokens 8\nhit 0\ncomputed 8\nrate 0\nblocks 0\nevicted 0\n", ""],
            [["stats"], "req a 1\n", 0, "requests 1\ntokens 1\nhit 0\ncomputed 1\nrate 0\nblocks 0\nevicted 0\n", ""],
            [["stats"], "req a 0 0 0 0\nreq b 0 0 0 0 0 0\nreq c 0 0\n", 0, "requests 3\ntokens 12\nhit 4\ncomputed 8\nrate 33\nblocks 1\nevicted 0\n", ""],
            [["stats", "--block", "2"], "req a 1 2 3 4 5 6 7 8 9\nreq b 1 2 3 4 5 6 7 8 9\nreq c 1 2 3 4 5 6 7 9 9\n", 0, "requests 3\ntokens 27\nhit 14\ncomputed 13\nrate 51\nblocks 5\nevicted 0\n", ""],
            [["stats", "--block", "2", "--cap", "2"], "req a 1 2 3 4\nreq b 5 6 7 8\nreq c 9 9 9 9\nreq d 1 2 3 4\n", 0, "requests 4\ntokens 16\nhit 0\ncomputed 16\nrate 0\nblocks 2\nevicted 6\n", ""],
            [["stats", "--block", "1", "--cap", "1"], "req a 1\nreq b 1\nreq c 2\n", 0, "requests 3\ntokens 3\nhit 1\ncomputed 2\nrate 33\nblocks 1\nevicted 1\n", ""],
            [["stats", "--block", "2"], "req a 1 2 3\nreq b 1 2 3\n", 0, "requests 2\ntokens 6\nhit 2\ncomputed 4\nrate 33\nblocks 1\nevicted 0\n", ""],
            [["stats", "--block", "2"], "req a 1 2\nreq b 1 2\nreq c 1 2\nreq d 1 2\n", 0, "requests 4\ntokens 8\nhit 6\ncomputed 2\nrate 75\nblocks 1\nevicted 0\n", ""],
        ];
    }
}

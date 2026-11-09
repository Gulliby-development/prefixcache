<?php

declare(strict_types=1);

namespace Prefixcache\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CacheRowsTest extends TestCase
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
            [["cache"], "", 0, "blocks 0 of 8\n", ""],
            [["cache"], "# nothing\n", 0, "blocks 0 of 8\n", ""],
            [["cache"], "req a 1 2 3 4 5 6 7 8\nreq b 1 2 3 4 9 9 9 9\nreq c 1 2 3 4 5 6 7 8 9\n", 0, "blocks 3 of 8\ndepth 2 tokens 5,6,7,8\ndepth 1 tokens 1,2,3,4\ndepth 2 tokens 9,9,9,9\n", ""],
            [["cache", "--block", "2"], "req a 1 2 3 4 5 6 7 8\nreq b 1 2 3 4 9 9 9 9\nreq c 1 2 3 4 5 6 7 8 9\n", 0, "blocks 6 of 8\ndepth 4 tokens 7,8\ndepth 3 tokens 5,6\ndepth 2 tokens 3,4\ndepth 1 tokens 1,2\ndepth 4 tokens 9,9\ndepth 3 tokens 9,9\n", ""],
            [["cache", "--block", "2"], "req a 1 2 3 4 5 6\nreq b 1 2 7 8\nreq c 1 2 3 4 9 9\nreq d 1 2 7 8 5 5\n", 0, "blocks 6 of 8\ndepth 3 tokens 5,5\ndepth 2 tokens 7,8\ndepth 1 tokens 1,2\ndepth 3 tokens 9,9\ndepth 2 tokens 3,4\ndepth 3 tokens 5,6\n", ""],
            [["cache", "--block", "2"], "req a 1 2\nreq b 1 2\nreq c 1 2 3\n", 0, "blocks 1 of 8\ndepth 1 tokens 1,2\n", ""],
            [["cache", "--block", "2"], "req a 1 2 3 4\nreq b 1 2\n", 0, "blocks 2 of 8\ndepth 1 tokens 1,2\ndepth 2 tokens 3,4\n", ""],
            [["cache", "--block", "2"], "req a 1 2 3 4\nreq b 5 6\nreq c 1 2 3 4\n", 0, "blocks 3 of 8\ndepth 2 tokens 3,4\ndepth 1 tokens 1,2\ndepth 1 tokens 5,6\n", ""],
            [["cache", "--block", "2", "--cap", "3"], "req a 1 2 3 4 5 6\nreq b 7 8\nreq c 9 9\nreq d 1 2\n", 0, "blocks 3 of 3\ndepth 1 tokens 1,2\ndepth 1 tokens 9,9\ndepth 1 tokens 7,8\n", ""],
            [["cache"], "req a 1 2 3 4\nflush\nreq b 1 2 3 4\nflush\nflush\n", 0, "blocks 0 of 8\n", ""],
            [["cache", "--block", "2"], "req a 1 2 3 4\nflush\nreq b 1 2 3 4\nflush\nflush\n", 0, "blocks 0 of 8\n", ""],
            [["trace"], "req a 1 2 3 4\nflush\nreq b 1 2 3 4\nflush\nflush\n", 0, "req a tokens 4 hit 0 new 1 evicted 0\nflush dropped 1\nreq b tokens 4 hit 0 new 1 evicted 0\nflush dropped 1\nflush dropped 0\n", ""],
            [["trace", "--block", "2"], "req a 1 2 3 4\nflush\nreq b 1 2 3 4\nflush\nflush\n", 0, "req a tokens 4 hit 0 new 2 evicted 0\nflush dropped 2\nreq b tokens 4 hit 0 new 2 evicted 0\nflush dropped 2\nflush dropped 0\n", ""],
            [["cache", "--block", "1"], "req a 4 4 4\nreq b 4 4 5\n", 0, "blocks 4 of 8\ndepth 3 tokens 5\ndepth 2 tokens 4\ndepth 1 tokens 4\ndepth 3 tokens 4\n", ""],
        ];
    }
}

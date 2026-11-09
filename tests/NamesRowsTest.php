<?php

declare(strict_types=1);

namespace Prefixcache\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NamesRowsTest extends TestCase
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
            [["check"], "req abcdefgh 1\n", 0, "ok 1 command\n", ""],
            [["check"], "req abcdefghi 1\n", 1, "", "prefixcache: line 1: bad name abcdefghi\n"],
            [["check"], "req a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\n", 1, "", "prefixcache: line 3: a is already used\n"],
            [["check"], "req a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\nreq a 1\nflush\n", 1, "", "prefixcache: script is too long (max 40 lines)\n"],
            [["check"], "flush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\nflush\n", 0, "ok 40 commands\n", ""],
        ];
    }
}

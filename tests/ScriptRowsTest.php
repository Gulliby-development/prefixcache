<?php

declare(strict_types=1);

namespace Prefixcache\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ScriptRowsTest extends TestCase
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
            [["trace"], "frob a 1\n", 1, "", "prefixcache: line 1: unknown command frob\n"],
            [["trace"], "req\n", 1, "", "prefixcache: line 1: bad arguments for req\n"],
            [["trace"], "req a\n", 1, "", "prefixcache: line 1: bad arguments for req\n"],
            [["trace"], "flush now\n", 1, "", "prefixcache: line 1: bad arguments for flush\n"],
            [["trace"], "REQ a 1\n", 1, "", "prefixcache: line 1: unknown command REQ\n"],
            [["trace"], "req A 1\n", 1, "", "prefixcache: line 1: bad name A\n"],
            [["trace"], "req 1a 1\n", 1, "", "prefixcache: line 1: bad name 1a\n"],
            [["trace"], "req a-b 1\n", 1, "", "prefixcache: line 1: bad name a-b\n"],
            [["trace"], "req a 1\nreq a 2\n", 1, "", "prefixcache: line 2: a is already used\n"],
            [["trace"], "req a 1\nflush\nreq a 2\n", 1, "", "prefixcache: line 3: a is already used\n"],
            [["trace"], "req a x\n", 1, "", "prefixcache: line 1: bad token x\n"],
            [["trace"], "req a 1 -1\n", 1, "", "prefixcache: line 1: bad token -1\n"],
            [["trace"], "req a 1 007\n", 1, "", "prefixcache: line 1: bad token 007\n"],
            [["trace"], "req a 1.5\n", 1, "", "prefixcache: line 1: bad token 1.5\n"],
            [["trace"], "req a 10000\n", 1, "", "prefixcache: line 1: bad token 10000\n"],
            [["trace"], "req a 9999\n", 0, "req a tokens 1 hit 0 new 0 evicted 0\n", ""],
            [["trace"], "# note\n\n  \nreq a 1\nbogus\n", 1, "", "prefixcache: line 5: unknown command bogus\n"],
            [["trace"], "req a 1\r\nreq b 2\r\nfrob\r\n", 1, "", "prefixcache: line 3: unknown command frob\n"],
            [["trace"], "req a 1 # x\n", 1, "", "prefixcache: line 1: bad token #\n"],
            [["trace"], "\treq\ta\t1\t2\t\n", 0, "req a tokens 2 hit 0 new 0 evicted 0\n", ""],
            [["check"], "req a 1\nreq b\nfrob\n", 1, "", "prefixcache: line 2: bad arguments for req\n"],
            [["check"], "req 1 1\nreq a x\n", 1, "", "prefixcache: line 1: bad name 1\n"],
        ];
    }
}

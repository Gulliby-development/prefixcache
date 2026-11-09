<?php

declare(strict_types=1);

namespace Prefixcache\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OptionsRowsTest extends TestCase
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
            [["trace", "x"], "", 2, "", "prefixcache: unexpected argument x\n"],
            [["trace", ""], "", 2, "", "prefixcache: unexpected argument \n"],
            [["cache", "-"], "", 2, "", "prefixcache: unexpected argument -\n"],
            [["check", "--nope"], "", 2, "", "prefixcache: unknown option --nope\n"],
            [["trace", "--block"], "", 2, "", "prefixcache: missing value for --block\n"],
            [["trace", "--block", "0"], "", 2, "", "prefixcache: bad value for --block\n"],
            [["trace", "--block", "007"], "", 2, "", "prefixcache: bad value for --block\n"],
            [["trace", "--block", "16"], "", 0, "", ""],
            [["trace", "--block", "17"], "", 2, "", "prefixcache: bad value for --block\n"],
            [["trace", "--block", "abc"], "", 2, "", "prefixcache: bad value for --block\n"],
            [["trace", "--block", ""], "", 2, "", "prefixcache: bad value for --block\n"],
            [["trace", "--cap"], "", 2, "", "prefixcache: missing value for --cap\n"],
            [["trace", "--cap", "0"], "", 2, "", "prefixcache: bad value for --cap\n"],
            [["trace", "--cap", "32"], "", 0, "", ""],
            [["trace", "--cap", "33"], "", 2, "", "prefixcache: bad value for --cap\n"],
            [["trace", "--cap", "99999999999"], "", 2, "", "prefixcache: bad value for --cap\n"],
            [["trace", "--cap", "-1"], "", 2, "", "prefixcache: bad value for --cap\n"],
            [["trace", "--block", "2", "--block", "3"], "", 2, "", "prefixcache: given twice: --block\n"],
            [["stats", "--cap", "4", "--cap", "5"], "", 2, "", "prefixcache: given twice: --cap\n"],
            [["trace", "--block", "--cap"], "", 2, "", "prefixcache: bad value for --block\n"],
            [["trace", "--cap", "--block", "2"], "", 2, "", "prefixcache: bad value for --cap\n"],
            [["trace", "--block", "2", "x"], "", 2, "", "prefixcache: unexpected argument x\n"],
            [["trace", "--cap", "3", "--block", "2"], "req a 1 2\n", 0, "req a tokens 2 hit 0 new 1 evicted 0\n", ""],
        ];
    }
}

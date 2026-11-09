<?php

declare(strict_types=1);

namespace Prefixcache\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ReadsRowsTest extends TestCase
{
    /**
     * @param list<string> $args
     */
    #[DataProvider('rows')]
    public function testRow(array $args, string $input, int $status, string $out, string $err): void
    {
        Harness::assertReads($args, $status);
    }

    /**
     * @return list<array{list<string>, string, int, string, string}>
     */
    public static function rows(): array
    {
        return [
            [["check"], "", 1, "", ""],
            [["trace"], "", 1, "", ""],
            [["cache"], "", 1, "", ""],
            [["stats"], "", 1, "", ""],
            [["trace", "--block", "2"], "", 1, "", ""],
            [["trace", "--cap", "3", "--block", "16"], "", 1, "", ""],
            [["stats", "--cap", "32"], "", 1, "", ""],
            [["help"], "", 0, "", ""],
            [["-h"], "", 0, "", ""],
            [["--help"], "", 0, "", ""],
            [["--version"], "", 0, "", ""],
            [[], "", 0, "", ""],
            [["frob"], "", 0, "", ""],
            [[""], "", 0, "", ""],
            [["trace", "x"], "", 0, "", ""],
            [["trace", "--block"], "", 0, "", ""],
            [["trace", "--block", "17"], "", 0, "", ""],
            [["trace", "--cap", "0"], "", 0, "", ""],
            [["trace", "--cap", "33"], "", 0, "", ""],
            [["trace", "--block", "2", "--block", "2"], "", 0, "", ""],
            [["check", "--bogus"], "", 0, "", ""],
            [["Trace"], "", 0, "", ""],
            [["--block", "2"], "", 0, "", ""],
            [["cache", "-"], "", 0, "", ""],
        ];
    }
}

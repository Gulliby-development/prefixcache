<?php

declare(strict_types=1);

namespace Prefixcache\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CliRowsTest extends TestCase
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
            [[], "", 2, "", "usage: prefixcache trace [--block N] [--cap N]\n       prefixcache cache [--block N] [--cap N]\n       prefixcache stats [--block N] [--cap N]\n       prefixcache check [--block N] [--cap N]\n"],
            [["help"], "", 0, "usage: prefixcache trace [--block N] [--cap N]\n       prefixcache cache [--block N] [--cap N]\n       prefixcache stats [--block N] [--cap N]\n       prefixcache check [--block N] [--cap N]\n", ""],
            [["-h", "extra"], "", 0, "usage: prefixcache trace [--block N] [--cap N]\n       prefixcache cache [--block N] [--cap N]\n       prefixcache stats [--block N] [--cap N]\n       prefixcache check [--block N] [--cap N]\n", ""],
            [["--help", "--bogus"], "", 0, "usage: prefixcache trace [--block N] [--cap N]\n       prefixcache cache [--block N] [--cap N]\n       prefixcache stats [--block N] [--cap N]\n       prefixcache check [--block N] [--cap N]\n", ""],
            [["--version"], "", 0, "prefixcache 0.1.0\n", ""],
            [["--version", "x"], "", 0, "prefixcache 0.1.0\n", ""],
            [["frob"], "", 2, "", "prefixcache: unknown command frob\n"],
            [[""], "", 2, "", "prefixcache: unknown command \n"],
            [["Trace"], "", 2, "", "prefixcache: unknown command Trace\n"],
            [["--block"], "", 2, "", "prefixcache: unknown command --block\n"],
            [["--cap", "4"], "", 2, "", "prefixcache: unknown command --cap\n"],
            [["help", "trace"], "", 0, "usage: prefixcache trace [--block N] [--cap N]\n       prefixcache cache [--block N] [--cap N]\n       prefixcache stats [--block N] [--cap N]\n       prefixcache check [--block N] [--cap N]\n", ""],
        ];
    }
}

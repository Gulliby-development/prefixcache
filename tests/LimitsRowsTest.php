<?php

declare(strict_types=1);

namespace Prefixcache\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LimitsRowsTest extends TestCase
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
            [["check"], "req r0 0\nreq r1 1\nreq r2 2\nreq r3 3\nreq r4 4\nreq r5 5\nreq r6 6\nreq r7 7\nreq r8 8\nreq r9 9\nreq r10 10\nreq r11 11\nreq r12 12\nreq r13 13\nreq r14 14\nreq r15 15\nreq r16 16\nreq r17 17\nreq r18 18\nreq r19 19\nreq r20 20\nreq r21 21\nreq r22 22\nreq r23 23\nreq r24 24\nreq r25 25\nreq r26 26\nreq r27 27\nreq r28 28\nreq r29 29\nreq r30 30\nreq r31 31\nreq r32 32\nreq r33 33\nreq r34 34\nreq r35 35\nreq r36 36\nreq r37 37\nreq r38 38\nreq r39 39\n", 0, "ok 40 commands\n", ""],
            [["check"], "req r0 0\nreq r1 1\nreq r2 2\nreq r3 3\nreq r4 4\nreq r5 5\nreq r6 6\nreq r7 7\nreq r8 8\nreq r9 9\nreq r10 10\nreq r11 11\nreq r12 12\nreq r13 13\nreq r14 14\nreq r15 15\nreq r16 16\nreq r17 17\nreq r18 18\nreq r19 19\nreq r20 20\nreq r21 21\nreq r22 22\nreq r23 23\nreq r24 24\nreq r25 25\nreq r26 26\nreq r27 27\nreq r28 28\nreq r29 29\nreq r30 30\nreq r31 31\nreq r32 32\nreq r33 33\nreq r34 34\nreq r35 35\nreq r36 36\nreq r37 37\nreq r38 38\nreq r39 39\nreq r40 40\n", 1, "", "prefixcache: script is too long (max 40 lines)\n"],
            [["check"], "# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n", 0, "ok 0 commands\n", ""],
            [["check"], "# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n# x\n", 1, "", "prefixcache: script is too long (max 40 lines)\n"],
            [["check"], "# top\nreq a 1\nbogus\nreq r0 0\nreq r1 1\nreq r2 2\nreq r3 3\nreq r4 4\nreq r5 5\nreq r6 6\nreq r7 7\nreq r8 8\nreq r9 9\nreq r10 10\nreq r11 11\nreq r12 12\nreq r13 13\nreq r14 14\nreq r15 15\nreq r16 16\nreq r17 17\nreq r18 18\nreq r19 19\nreq r20 20\nreq r21 21\nreq r22 22\nreq r23 23\nreq r24 24\nreq r25 25\nreq r26 26\nreq r27 27\nreq r28 28\nreq r29 29\nreq r30 30\nreq r31 31\nreq r32 32\nreq r33 33\nreq r34 34\nreq r35 35\nreq r36 36\nreq r37 37\nreq r38 38\nreq r39 39\n", 1, "", "prefixcache: script is too long (max 40 lines)\n"],
        ];
    }
}

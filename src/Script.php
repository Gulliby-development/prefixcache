<?php

declare(strict_types=1);

namespace Prefixcache;

/**
 * Splits and validates the physical lines of a replay script.
 */
final class Script
{
    /**
     * @return list<string>
     */
    public static function lines(string $input): array
    {
        if ($input === '') {
            return [];
        }

        $lines = explode("\n", $input);
        if (str_ends_with($input, "\n")) {
            array_pop($lines);
        }

        if (count($lines) > Registry::MAX_LINES) {
            throw new Failure(1, 'script is too long (max ' . Registry::MAX_LINES . ' lines)');
        }

        foreach ($lines as $index => $line) {
            if (str_ends_with($line, "\r")) {
                $lines[$index] = substr($line, 0, -1);
            }
        }

        return $lines;
    }

    /**
     * @param array<string, true> $used
     * @return array{kind: 'flush'}|array{kind: 'req', id: string, tokens: list<string>}|null
     */
    public static function command(string $line, int $number, array $used): ?array
    {
        $trimmed = trim($line, " \t");
        if ($trimmed === '') {
            return null;
        }

        $words = preg_split('/[ \t]+/', $trimmed);
        if ($words === false) {
            return null;
        }

        $command = $words[0];
        if (str_starts_with($command, '#')) {
            return null;
        }

        if ($command !== 'req' && $command !== 'flush') {
            self::fail($number, 'unknown command ' . $command);
        }

        if ($command === 'flush') {
            if (count($words) !== 1) {
                self::fail($number, 'bad arguments for flush');
            }

            return ['kind' => 'flush'];
        }

        if (count($words) < 3) {
            self::fail($number, 'bad arguments for req');
        }

        $id = $words[1];
        if (preg_match('/\\A[a-z][a-z0-9_]{0,7}\\z/', $id) !== 1) {
            self::fail($number, 'bad name ' . $id);
        }
        if (isset($used[$id])) {
            self::fail($number, $id . ' is already used');
        }

        $tokenWords = array_slice($words, 2);
        $tokens = [];
        foreach ($tokenWords as $token) {
            if (preg_match('/\\A(?:0|[1-9][0-9]{0,3})\\z/', $token) !== 1 || (int) $token > 9999) {
                self::fail($number, 'bad token ' . $token);
            }
            $tokens[] = $token;
        }

        if (count($tokens) > Registry::MAX_TOKENS) {
            self::fail($number, 'too many tokens (max ' . Registry::MAX_TOKENS . ')');
        }

        return ['kind' => 'req', 'id' => $id, 'tokens' => $tokens];
    }

    private static function fail(int $number, string $reason): never
    {
        throw new Failure(1, 'line ' . $number . ': ' . $reason);
    }
}

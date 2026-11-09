<?php

declare(strict_types=1);

namespace Prefixcache;

/**
 * @phpstan-type Reply array{status: int, out: string, err: string}
 * @phpstan-type Options array{block: int, cap: int}
 * @phpstan-type Selection array{command: string, handler: string, options: Options}
 * @phpstan-type Validation array{selection: Selection|null, error: string}
 */
final class Cli
{
    /**
     * @param list<string> $args
     * @return array{status: int, out: string, err: string}
     */
    public static function run(array $args, string $input): array
    {
        if ($args === []) {
            return ['status' => 2, 'out' => '', 'err' => Registry::USAGE];
        }

        if (in_array($args[0], ['help', '-h', '--help'], true)) {
            return ['status' => 0, 'out' => Registry::USAGE, 'err' => ''];
        }
        if ($args[0] === '--version') {
            return ['status' => 0, 'out' => 'prefixcache ' . Registry::VERSION . "\n", 'err' => ''];
        }

        $validation = self::validate($args);
        if ($validation['selection'] === null) {
            return ['status' => 2, 'out' => '', 'err' => 'prefixcache: ' . $validation['error'] . "\n"];
        }

        try {
            $selection = $validation['selection'];
            $reply = self::dispatch($selection['handler'], $selection['options'], $input);
            return $reply;
        } catch (Failure $failure) {
            return ['status' => $failure->status(), 'out' => '', 'err' => 'prefixcache: ' . $failure->getMessage() . "\n"];
        }
    }

    /**
     * @param list<string> $args
     */
    public static function readsStdin(array $args): bool
    {
        return self::validate($args)['selection'] !== null;
    }

    /**
     * @param list<string> $args
     * @return Validation
     */
    private static function validate(array $args): array
    {
        if ($args === []) {
            return ['selection' => null, 'error' => ''];
        }

        $command = null;
        foreach (Registry::COMMANDS as $entry) {
            if ($entry['name'] === $args[0]) {
                $command = $entry;
                break;
            }
        }
        if ($command === null) {
            return ['selection' => null, 'error' => 'unknown command ' . $args[0]];
        }

        $options = ['block' => Registry::DEFAULT_BLOCK, 'cap' => Registry::DEFAULT_CAP];
        $seenBlock = false;
        $seenCap = false;
        $count = count($args);
        for ($index = 1; $index < $count; $index++) {
            $word = $args[$index];
            if ($word !== '--block' && $word !== '--cap') {
                if ($word === '' || $word === '-' || $word[0] !== '-') {
                    return ['selection' => null, 'error' => 'unexpected argument ' . $word];
                }
                return ['selection' => null, 'error' => 'unknown option ' . $word];
            }

            if ($word === '--block') {
                if ($seenBlock) {
                    return ['selection' => null, 'error' => 'given twice: --block'];
                }
                $seenBlock = true;
                if ($index + 1 >= $count) {
                    return ['selection' => null, 'error' => 'missing value for --block'];
                }
                $value = $args[++$index];
                if (!self::validNumber($value, Registry::MAX_BLOCK)) {
                    return ['selection' => null, 'error' => 'bad value for --block'];
                }
                $options['block'] = (int) $value;
                continue;
            }

            if ($seenCap) {
                return ['selection' => null, 'error' => 'given twice: --cap'];
            }
            $seenCap = true;
            if ($index + 1 >= $count) {
                return ['selection' => null, 'error' => 'missing value for --cap'];
            }
            $value = $args[++$index];
            if (!self::validNumber($value, Registry::MAX_CAP)) {
                return ['selection' => null, 'error' => 'bad value for --cap'];
            }
            $options['cap'] = (int) $value;
        }

        return [
            'selection' => ['command' => $command['name'], 'handler' => $command['handler'], 'options' => $options],
            'error' => '',
        ];
    }

    private static function validNumber(string $value, int $maximum): bool
    {
        if ($value === '' || strlen($value) > 2 || preg_match('/\\A[1-9][0-9]*\\z/', $value) !== 1) {
            return false;
        }

        return (int) $value <= $maximum;
    }

    /**
     * @param Options $options
     * @return Reply
     */
    private static function dispatch(string $handler, array $options, string $input): array
    {
        return match ($handler) {
            'trace' => Commands::trace($options, $input),
            'cache' => Commands::cache($options, $input),
            'stats' => Commands::stats($options, $input),
            'check' => Commands::check($options, $input),
            default => throw new Failure(2, 'unknown command handler'),
        };
    }
}

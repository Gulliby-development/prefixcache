<p align="center"><img src="docs/logo.svg" width="96" alt="" /></p>

# prefixcache

[![CI](https://github.com/Gulliby-development/prefixcache/actions/workflows/ci.yml/badge.svg)](https://github.com/Gulliby-development/prefixcache/actions/workflows/ci.yml) [![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

prefixcache is a small, offline PHP 8.4 command-line simulator for replaying model-prompt requests against a fixed-size block cache. It is for people building or tuning model servers and for anyone planning prompts that share prefixes. It uses only PHP's standard library at runtime and works on Windows, macOS, and Linux.

The commands read a request script from standard input. `trace` shows each request's cache outcome, `cache` lists the blocks left at the end, `stats` summarizes token reuse, and `check` validates the script without reporting cache details. The project is distributed under the [MIT License](LICENSE).

## Run

Use PHP 8.4. From the repository directory, run a command as:

```sh
php bin/prefixcache COMMAND
```

Replace `COMMAND` with `trace`, `cache`, `stats`, or `check`. These four commands consume standard input. They do not access the network.

For convenient use in a POSIX shell, define `prefixcache` as a shell function that runs `php bin/prefixcache "$@"`:

```sh
prefixcache() { php bin/prefixcache "$@"; }
```

The examples below assume that function is defined and that the shell is in the repository directory. In the examples, `prefixcache` means this function.

## Command synopsis

```text
usage: prefixcache trace [--block N] [--cap N]
       prefixcache cache [--block N] [--cap N]
       prefixcache stats [--block N] [--cap N]
       prefixcache check [--block N] [--cap N]
```

The default block size is 4 tokens and the default cache capacity is 8 blocks. `--block N` accepts decimal values from 1 through 16; `--cap N` accepts decimal values from 1 through 32. Values cannot have leading zeroes. Both options may be supplied in either order, once each.

The first word `help`, `-h`, or `--help` prints the synopsis and exits successfully, ignoring later words. `--version` prints `prefixcache 0.1.0` and exits successfully, also ignoring later words. With no command, the synopsis is written to standard error and the process exits with status 2. An unrecognized first word (including an empty word) produces `prefixcache: unknown command WORD`.

Arguments are validated from left to right; the first error is reported and the script is not read. A non-option word (including an empty word or `-`) produces `prefixcache: unexpected argument WORD`. An unsupported option produces `prefixcache: unknown option WORD`. Each supported option takes the next word as its value, even if that word resembles an option. For a repeated option, the duplicate error takes precedence over a missing or invalid value: `prefixcache: given twice: OPTION`. Otherwise a final option without a value gives `prefixcache: missing value for OPTION`, and an invalid value gives `prefixcache: bad value for OPTION`.

## Script format

The script is split at line feeds. A final unterminated line is still processed, and one carriage return immediately before a line feed (or at the end of the final line) is removed. The entire script is checked for the 40-line maximum before any line is interpreted: 40 lines are allowed, while 41 produces `prefixcache: script is too long (max 40 lines)`. Thus this length error takes precedence over errors on individual lines.

Words are separated by one or more spaces or tabs; spaces and tabs at either end are ignored. A blank line or a line whose first word begins with `#` is skipped. Inline comments are not supported. Line numbers include skipped lines.

There are two commands:

- `req ID TOKEN...` submits a request and needs at least one token. An ID is 1–8 characters: a lowercase letter followed by lowercase letters, digits, or underscores. Each ID may be used only once in a script, including after a `flush`.
- `flush` removes all cached blocks and takes no arguments.

A token is a decimal integer from 0 through 9999, with no leading zero except for the token `0`. Each request may contain at most 48 tokens.

Validation is ordered. The first word must be `req` or `flush`, otherwise the error is `unknown command WORD`. A request with too few words gives `bad arguments for req`; then its ID is checked (`bad name WORD`, followed by `ID is already used`); then tokens are checked left to right (`bad token WORD`); finally the request token limit is checked (`too many tokens (max 48)`). Extra words after `flush` give `bad arguments for flush`. Script errors include their line number as `prefixcache: line N: REASON`.

A failing script command writes no partial report: standard output is empty, one error line is written to standard error, and the process exits with status 1. Invalid command-line arguments likewise leave standard output empty and write one error line to standard error with status 2; with no command, the synopsis is written to standard error and status 2 is returned. Successful commands write their report to standard output, nothing to standard error, and exit with status 0.

## Cache behavior

A block contains the configured number of tokens. A cached block represents the complete request prefix through that block, not just the tokens within the block; consequently equal tokens at different prefixes identify different blocks. Only complete blocks are cached, so a trailing partial block is ignored. Cached blocks always have their parent prefix block present.

For each request, blocks are checked from depth 1 upward. The hit is the contiguous run of cached prefix blocks from the beginning, stopping at the first miss. Hit tokens equal the number of hit blocks multiplied by the block size. Each hit block is touched in increasing depth order. New complete blocks are then considered in increasing depth order; every touch or insertion advances the cache's increasing clock.

When the cache is full, an insertion first evicts the least-recently-touched eligible leaf (a block with no child). Blocks hit or inserted earlier in the same request are protected from eviction. If no eligible leaf exists, insertion stops and remaining blocks from that request are not cached. A `flush` drops all blocks but is not counted as an eviction.

## Reports

- `check` prints `ok N command` for one executed script command, or `ok N commands` otherwise. Blank and comment lines do not count.
- `trace` prints one LF-terminated line per executed command: `req ID tokens N hit H new K evicted E` for a request, or `flush dropped K` for a flush. For a request, `N` is its number of tokens, `H` is the number of served tokens, `K` is the number of new blocks cached, and `E` is the number of blocks evicted while serving that request. For a flush, `K` is the number of blocks removed. A script with no commands produces no trace lines.
- `cache` prints LF-terminated lines, beginning with `blocks N of CAP`, where `N` is the number of blocks in the cache at the end and `CAP` is its configured capacity. Each remaining line is `depth D tokens T1,T2,...`, ordered from most recently touched block to least recently touched. `D` is that block's depth in its request prefix; the token list contains only that block's tokens, comma-separated without spaces.
- `stats` prints seven LF-terminated lines: `requests R`, `tokens T`, `hit H`, `computed C`, `rate P`, `blocks B`, and `evicted E`. `R` counts all `req` commands; `T` is the cumulative number of submitted tokens; `H` is the cumulative number of served tokens; `C` is computed tokens, calculated as `T - H`; `P` is the whole-number percentage `floor(H * 100 / T)`, or `0` when `T` is zero; `B` is the number of blocks cached at the end; and `E` is the cumulative number of blocks evicted while serving requests. A flush does not count as an eviction.

## Examples

These four transcripts show the exact output. In the last example, `2>&1` sends the error lines to standard output so they appear in the transcript.

```text
$ printf 'req a 1 2 3 4 5 6 7 8\nreq b 1 2 3 4 9 9 9 9\nreq c 1 2 3 4 5 6 7 8 9\n' | prefixcache trace
req a tokens 8 hit 0 new 2 evicted 0
req b tokens 8 hit 4 new 1 evicted 0
req c tokens 9 hit 8 new 0 evicted 0
$ printf 'req a 1 2 3 4 5 6\nreq b 7 8 9 9\nreq c 1 2 5 5\n' | prefixcache cache --block 2 --cap 3
blocks 3 of 3
depth 2 tokens 5,5
depth 1 tokens 1,2
depth 1 tokens 7,8
$ printf 'req a 1 2 3 4 5 6 7 8\nreq b 1 2 3 4 5 6 7 8\nflush\nreq c 1 2 3 4\n' | prefixcache stats --block 2
requests 3
tokens 20
hit 8
computed 12
rate 40
blocks 2
evicted 0
$ printf 'req a 1\nreq a 2\n' | prefixcache check 2>&1; printf 'req a 1\n' | prefixcache check --cap 33 2>&1
prefixcache: line 2: a is already used
prefixcache: bad value for --cap
```

## Development

The scaffold commits PHPUnit, PHPStan, and Pint as development tools. Run the project checks from the repository directory:

```sh
php vendor/bin/pint --test
php vendor/bin/phpstan analyse --no-progress
php vendor/bin/phpunit
```

The application entry point is `bin/prefixcache`; implementation classes live under `src/` in the `Prefixcache` namespace, and literal row-based tests live under `tests/`. The code uses strict types, typed array shapes, and PSR-12 formatting. Classes receive script input as a string and return replies rather than reading or writing streams; stream I/O belongs to the entry point. Preserve the supplied class layout, test wrappers and literal rows, and scaffold configuration. Runtime dependencies remain limited to PHP's standard library.

---

Built with [BAKED](https://github.com/Gulliby-development/baked), an open-source coding agent (bench round R94).

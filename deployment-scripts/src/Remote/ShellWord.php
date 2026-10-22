<?php

declare(strict_types=1);

namespace Ecms\Deployment\Remote;

/**
 * Escapes a single argument for a POSIX remote shell.
 *
 * The algorithm is the one drush itself uses
 * (Consolidation\SiteProcess\Util\Escape::linuxArg()), inlined here rather
 * than depended on: it is three operations, and this tool should not couple
 * itself to a drush internal just to reach them.
 *
 * Unlike escapeshellarg(), this is locale- and encoding-independent, which
 * matters because the escaped string is interpreted by the *remote* shell,
 * not the local one.
 *
 * Drush's version additionally rewrites control characters to spaces. That
 * branch is deliberately omitted: DrushCommandLine rejects control
 * characters outright rather than silently rewriting an operator's input.
 */
final class ShellWord {

  /**
   * Wraps an argument in single quotes, escaping any it contains.
   *
   * Inside single quotes a POSIX shell treats every byte literally, so the
   * only character needing attention is the single quote itself: close the
   * quote, emit an escaped quote, reopen. 'it's' becomes 'it'\''s'.
   *
   * @param string $argument
   *   The raw argument.
   *
   * @return string
   *   The argument, safe to concatenate into a remote command string.
   */
  public static function escape(string $argument): string {
    return "'" . str_replace("'", "'\\''", $argument) . "'";
  }

  /**
   * Escapes every argument and joins them into one command string.
   *
   * @param string[] $arguments
   *   The argv to escape, command name first.
   *
   * @return string
   *   A single command string for the remote shell.
   */
  public static function join(array $arguments): string {
    return implode(' ', array_map([self::class, 'escape'], $arguments));
  }

}

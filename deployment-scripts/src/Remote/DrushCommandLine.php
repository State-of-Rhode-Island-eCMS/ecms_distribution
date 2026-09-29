<?php

declare(strict_types=1);

namespace Ecms\Deployment\Remote;

use Symfony\Component\Console\Input\StringInput;

/**
 * One validated, tokenized drush command from a single --cmd option.
 *
 * Named DrushCommandLine, not DrushCommand, so it can never be confused
 * with a Symfony Console Command.
 *
 * This class is the first half of the escaping story. It splits operator
 * input into argv locally — "sapi-sis acquia_search_index searchstax" must
 * reach drush as three arguments, not one — and rejects anything that is
 * not plausibly a drush command. ShellWord::escape() then guarantees no
 * token can escape its quotes on the remote side.
 *
 * There is deliberately no allowlist of drush verbs: the requirement is
 * "any drush command". The protection is that a hostile --cmd can at worst
 * run a *drush* command, which the operator could equally do by hand over
 * the same SSH key they already hold.
 */
final class DrushCommandLine {

  /**
   * A plausible drush command name or alias, e.g. "sapi-i", "state:set".
   *
   * Anchored and leading-letter-only, so "; rm -rf /" and "-v" are refused
   * before anything is escaped or connected to.
   */
  private const COMMAND_NAME_PATTERN = '/^[a-zA-Z][a-zA-Z0-9:._-]*$/';

  /**
   * Control characters that are rejected rather than silently rewritten.
   *
   * Drush's own escaper turns these into spaces; failing loudly is better
   * than quietly changing what the operator asked for. Tab is included
   * deliberately: the tokenizer would treat it as a separator, so
   * "cr\tsapi-i" would silently become two commands crammed into one --cmd
   * rather than the error it plainly is. Ordinary spaces remain the only
   * accepted separator.
   */
  private const CONTROL_CHARACTERS = '/[\x00-\x1F\x7F]/';

  /**
   * Constructs a DrushCommandLine.
   *
   * @param string $raw
   *   The --cmd value exactly as typed, for display and audit lines.
   * @param string[] $argv
   *   The tokenized arguments, command name first.
   */
  private function __construct(
    public readonly string $raw,
    public readonly array $argv,
  ) {}

  /**
   * Tokenizes and validates one --cmd value.
   *
   * @param string $value
   *   The raw --cmd value.
   *
   * @throws \InvalidArgumentException
   *   If the value is empty, contains control characters, or does not start
   *   with something that looks like a drush command name. Every message
   *   names the offending --cmd.
   */
  public static function fromString(string $value): self {
    if (trim($value) === '') {
      throw new \InvalidArgumentException('Empty --cmd. Each --cmd must be a drush command, e.g. --cmd="sapi-i".');
    }

    if (preg_match(self::CONTROL_CHARACTERS, $value) === 1) {
      throw new \InvalidArgumentException(sprintf(
        'Invalid drush command in --cmd "%s": control characters (newlines, tabs, NUL) are not allowed.',
        self::printable($value)
      ));
    }

    $argv = self::tokenize($value);
    if ($argv === []) {
      throw new \InvalidArgumentException(sprintf('Empty --cmd "%s".', $value));
    }

    if (preg_match(self::COMMAND_NAME_PATTERN, $argv[0]) !== 1) {
      throw new \InvalidArgumentException(sprintf(
        'Invalid drush command in --cmd "%s": it must start with a drush command name, got "%s". '
        . 'Shell characters and leading options are not allowed.',
        $value,
        self::printable($argv[0])
      ));
    }

    return new self($value, $argv);
  }

  /**
   * The command name, for the results table.
   */
  public function name(): string {
    return $this->argv[0];
  }

  /**
   * The raw --cmd value, for display.
   */
  public function __toString(): string {
    return $this->raw;
  }

  /**
   * Splits a command string into argv, honouring quotes and backslashes.
   *
   * PHP has no shell-word splitter, and StringInput::tokenize() is private,
   * but its regexes are public — so this follows the same approach with the
   * same constants rather than inventing a dialect.
   *
   * @return string[]
   *   The tokens.
   */
  private static function tokenize(string $input): array {
    $tokens = [];
    $length = strlen($input);
    $cursor = 0;
    $token = NULL;

    while ($cursor < $length) {
      if ($input[$cursor] === '\\') {
        $token .= $input[$cursor + 1] ?? '';
        $cursor += 2;
        continue;
      }

      if (preg_match('/\s+/A', $input, $match, 0, $cursor) === 1) {
        if ($token !== NULL) {
          $tokens[] = $token;
          $token = NULL;
        }
      }
      elseif (preg_match('/([^="\'\s]+?)(=?)(' . StringInput::REGEX_QUOTED_STRING . '+)/A', $input, $match, 0, $cursor) === 1) {
        $token .= $match[1] . $match[2] . stripcslashes(str_replace(['"\'', '\'"', '\'\'', '""'], '', substr($match[3], 1, -1)));
      }
      elseif (preg_match('/' . StringInput::REGEX_QUOTED_STRING . '/A', $input, $match, 0, $cursor) === 1) {
        $token .= stripcslashes(substr($match[0], 1, -1));
      }
      elseif (preg_match('/' . StringInput::REGEX_UNQUOTED_STRING . '/A', $input, $match, 0, $cursor) === 1) {
        $token .= $match[1];
      }
      else {
        throw new \InvalidArgumentException(sprintf(
          'Unable to parse --cmd near "... %s ...".',
          substr($input, $cursor, 10)
        ));
      }

      $cursor += strlen($match[0]);
    }

    if ($token !== NULL) {
      $tokens[] = $token;
    }

    return $tokens;
  }

  /**
   * Renders a value safely for an error message.
   */
  private static function printable(string $value): string {
    return addcslashes($value, "\0..\37\177");
  }

}

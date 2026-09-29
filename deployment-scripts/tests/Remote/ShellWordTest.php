<?php

declare(strict_types=1);

namespace Ecms\Deployment\Tests\Remote;

use Ecms\Deployment\Remote\ShellWord;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * Escaping is the security boundary of ecms:drush:run, so it is asserted
 * against a real shell rather than against a hand-written expected string.
 * Comparing to a literal would only restate the implementation; running the
 * result through sh proves the shell sees exactly the original bytes.
 */
final class ShellWordTest extends TestCase {

  /**
   * @return array<string, array{string}>
   */
  public static function argumentProvider(): array {
    return [
      'plain' => ['sapi-i'],
      'spaces' => ['a b'],
      'semicolon and comment' => ['a; id #'],
      'command substitution' => ['$(whoami)'],
      'backticks' => ['`id`'],
      'single quote' => ["it's"],
      'double quote' => ['a"b'],
      'backslash' => ['a\\b'],
      'option with spaces' => ['--root=/x y/z'],
      'ampersands' => ['a && b || c'],
      'pipe and redirect' => ['a | b > c'],
      'newline' => ["a\nb"],
      'dollar var' => ['$HOME'],
      'empty' => [''],
    ];
  }

  /**
   * @dataProvider argumentProvider
   */
  public function testEscapedArgumentSurvivesARealShell(string $argument): void {
    // Process::getOutput() returns an empty string for no output, where
    // shell_exec() would return NULL and make the empty case look broken.
    $process = new Process(['sh', '-c', 'printf %s ' . ShellWord::escape($argument)]);
    $process->run();

    $this->assertSame(0, $process->getExitCode());
    $this->assertSame($argument, $process->getOutput());
  }

  public function testEmptyStringBecomesQuotedEmpty(): void {
    // Not the empty string, or the argument would vanish from argv.
    $this->assertSame("''", ShellWord::escape(''));
  }

  public function testSingleQuoteUsesTheCloseEscapeReopenForm(): void {
    $this->assertSame("'it'\\''s'", ShellWord::escape("it's"));
  }

  public function testJoinEscapesEveryArgument(): void {
    $this->assertSame(
      "'drush' '--root=/a b' 'sapi-i'",
      ShellWord::join(['drush', '--root=/a b', 'sapi-i'])
    );
  }

  public function testJoinedCommandPassesArgumentsThroughUnchanged(): void {
    $arguments = ['printf', '%s|%s|%s', 'a; id', '$(whoami)'];
    $process = new Process(['sh', '-c', ShellWord::join($arguments)]);
    $process->run();

    $this->assertSame('a; id|$(whoami)|', $process->getOutput());
  }

}

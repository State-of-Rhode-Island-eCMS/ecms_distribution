<?php

declare(strict_types=1);

namespace Ecms\Deployment\Tests\Remote;

use Ecms\Deployment\Remote\DrushCommandLine;
use PHPUnit\Framework\TestCase;

final class DrushCommandLineTest extends TestCase {

  public function testSingleWordCommand(): void {
    $this->assertSame(['sapi-i'], DrushCommandLine::fromString('sapi-i')->argv);
  }

  public function testCommandWithArgumentsIsSplitIntoArgv(): void {
    $this->assertSame(
      ['sapi-sis', 'acquia_search_index', 'searchstax'],
      DrushCommandLine::fromString('sapi-sis acquia_search_index searchstax')->argv
    );
  }

  public function testQuotedArgumentStaysOneToken(): void {
    $this->assertSame(
      ['state:set', 'foo', 'bar baz'],
      DrushCommandLine::fromString('state:set foo "bar baz"')->argv
    );
  }

  public function testRepeatedWhitespaceIsCollapsed(): void {
    $this->assertSame(
      ['sapi-i', 'acquia_search_index'],
      DrushCommandLine::fromString("sapi-i    acquia_search_index  ")->argv
    );
  }

  public function testOptionsArePreserved(): void {
    $this->assertSame(
      ['sapi-i', '--batch-size=10', 'acquia_search_index'],
      DrushCommandLine::fromString('sapi-i --batch-size=10 acquia_search_index')->argv
    );
  }

  public function testColonCommandNameIsAccepted(): void {
    $this->assertSame('search-api:index', DrushCommandLine::fromString('search-api:index')->name());
  }

  public function testRawIsPreservedVerbatimForDisplay(): void {
    $raw = 'state:set foo "bar baz"';
    $command = DrushCommandLine::fromString($raw);
    $this->assertSame($raw, $command->raw);
    $this->assertSame($raw, (string) $command);
  }

  /**
   * @return array<string, array{string}>
   */
  public static function rejectedProvider(): array {
    return [
      'empty' => [''],
      'whitespace only' => ['   '],
      'leading semicolon' => ['; rm -rf /'],
      'trailing chain' => ['cr; rm -rf /'],
      'and chain' => ['&& id'],
      'leading option' => ['-v'],
      'long option' => ['--version'],
      'command substitution' => ['$(whoami)'],
      'backticks' => ['`id`'],
      'pipe' => ['| id'],
      'redirect' => ['> /etc/passwd'],
      'newline' => ["cr\nsapi-i"],
      'carriage return' => ["cr\r"],
      'tab' => ["cr\tsapi-i"],
      'nul byte' => ["a\0b"],
    ];
  }

  /**
   * @dataProvider rejectedProvider
   */
  public function testRejectedValues(string $value): void {
    $this->expectException(\InvalidArgumentException::class);
    DrushCommandLine::fromString($value);
  }

  public function testRejectionNamesTheOffendingCmd(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('#--cmd "cr; rm -rf /"#');
    DrushCommandLine::fromString('cr; rm -rf /');
  }

  public function testControlCharacterRejectionIsExplicit(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('#control characters#');
    DrushCommandLine::fromString("cr\nsapi-i");
  }

  public function testShellMetacharactersSurviveInsideArguments(): void {
    // Metacharacters in *arguments* are fine — only the command name is
    // constrained. ShellWord::escape() neutralises them on the remote side.
    $command = DrushCommandLine::fromString('state:set evil "a; id"');
    $this->assertSame(['state:set', 'evil', 'a; id'], $command->argv);
  }

}

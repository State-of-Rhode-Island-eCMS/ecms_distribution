<?php

declare(strict_types=1);

namespace Ecms\Deployment\Tests\Command;

use Ecms\Deployment\Command\AbstractAcsfCommand;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * AbstractAcsfCommand::parseSiteIds() is protected and shared by every
 * command; exercised here directly via a minimal concrete subclass rather
 * than through a specific command's CommandTester.
 */
final class ParseSiteIdsTest extends TestCase {

  private function command(): AbstractAcsfCommand {
    return new class() extends AbstractAcsfCommand {

      protected function execute(InputInterface $input, OutputInterface $output): int {
        return 0;
      }

      public function parse(string $value): array {
        return $this->parseSiteIds($value);
      }

    };
  }

  public function testValidListIsNormalized(): void {
    $this->assertSame([111, 222, 333], $this->command()->parse('111, 222, 333'));
  }

  public function testDuplicatesAreRemoved(): void {
    $this->assertSame([111, 222], $this->command()->parse('111,222,111'));
  }

  public function testInvalidEntryNamesTheOffender(): void {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid site ID(s) in --sites: abc.');
    $this->command()->parse('111,abc,333');
  }

  public function testZeroAndNegativeAreInvalid(): void {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid site ID(s) in --sites: 0, -5.');
    $this->command()->parse('0,-5');
  }

}

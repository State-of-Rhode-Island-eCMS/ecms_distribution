<?php

declare(strict_types=1);

namespace Ecms\Deployment\Tests\Command;

use Ecms\Deployment\Command\BackupPruneCommand;
use Ecms\Deployment\Tests\MockClientTrait;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class BackupPruneCommandTest extends TestCase {

  use MockClientTrait;

  private function tester(MockHandler $mock): CommandTester {
    $command = new BackupPruneCommand($this->mockedClient($mock));
    return new CommandTester($command);
  }

  public function testDryRunIssuesNoDelete(): void {
    $mock = new MockHandler([
      // resolveSites(): find(1).
      new Response(200, [], json_encode(['id' => 1, 'domain' => 'site1.example.com'])),
      // backupRepository->forSite(1): one backup old enough to be pruned.
      new Response(200, [], json_encode([
        'backups' => [['id' => 5, 'label' => 'old', 'timestamp' => 1]],
      ])),
    ]);

    $tester = $this->tester($mock);
    $exitCode = $tester->execute([
      '--sites' => '1',
      '--retention-days' => '1',
      '--dry-run' => TRUE,
    ], ['interactive' => FALSE]);

    $this->assertSame(Command::SUCCESS, $exitCode);
    $this->assertSame(['GET', 'GET'], $this->recordedMethods());
    $this->assertStringContainsString('would delete', $tester->getDisplay());
  }

  public function testProdWithoutExplicitConfirmationFlagIssuesNoDelete(): void {
    $mock = new MockHandler([
      new Response(200, [], json_encode(['id' => 1, 'domain' => 'site1.example.com'])),
      new Response(200, [], json_encode([
        'backups' => [['id' => 5, 'label' => 'old', 'timestamp' => 1]],
      ])),
    ]);

    $tester = $this->tester($mock);

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('--i-know-this-is-production');
    try {
      $tester->execute([
        '--prod' => TRUE,
        '--sites' => '1',
        '--force' => TRUE,
      ], ['interactive' => FALSE]);
    }
    finally {
      $this->assertSame([], $this->recordedMethods(), 'No API request should have been made before the guardrail is satisfied.');
    }
  }

  public function testNonInteractiveWithoutForceIsRefused(): void {
    $mock = new MockHandler([]);
    $tester = $this->tester($mock);

    $this->expectException(InvalidArgumentException::class);
    try {
      $tester->execute(['--sites' => '1'], ['interactive' => FALSE]);
    }
    finally {
      $this->assertSame([], $this->recordedMethods());
    }
  }

  public function testDeletesBackupsOlderThanRetention(): void {
    $mock = new MockHandler([
      new Response(200, [], json_encode(['id' => 1, 'domain' => 'site1.example.com'])),
      new Response(200, [], json_encode([
        'backups' => [['id' => 5, 'label' => 'old', 'timestamp' => 1]],
      ])),
      new Response(200, [], json_encode(['task_id' => 42])),
    ]);

    $tester = $this->tester($mock);
    $exitCode = $tester->execute([
      '--sites' => '1',
      '--retention-days' => '1',
      '--force' => TRUE,
    ], ['interactive' => FALSE]);

    $this->assertSame(Command::SUCCESS, $exitCode);
    $this->assertSame(['GET', 'GET', 'DELETE'], $this->recordedMethods());
  }

}

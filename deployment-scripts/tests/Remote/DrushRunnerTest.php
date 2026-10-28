<?php

declare(strict_types=1);

namespace Ecms\Deployment\Tests\Remote;

use Ecms\Deployment\Api\Site;
use Ecms\Deployment\Config\FactoryEnvironment;
use Ecms\Deployment\Remote\DrushCommandLine;
use Ecms\Deployment\Remote\DrushRunner;
use Ecms\Deployment\Remote\ProcessOutcome;
use Ecms\Deployment\Remote\SiteAlias;
use Ecms\Deployment\Remote\SiteAliasLoader;
use Ecms\Deployment\Remote\StepStatus;
use PHPUnit\Framework\TestCase;

final class DrushRunnerTest extends TestCase {

  private const CONTROL_DIR = '/tmp/ecms-test-ctl';

  private function alias(string $scenario = 'valid', ?string $siteName = NULL): SiteAlias {
    $loader = new SiteAliasLoader(__DIR__ . '/../fixtures/drush-sites/' . $scenario);
    return $loader->load(FactoryEnvironment::Prod, $siteName);
  }

  private function site(): Site {
    return new Site(123, 'abc.example.invalid', 'abc');
  }

  /**
   * @param \Ecms\Deployment\Remote\DrushCommandLine[]|string[] $commands
   */
  private function commands(array $commands): array {
    return array_map(
      static fn (string $c): DrushCommandLine => DrushCommandLine::fromString($c),
      $commands
    );
  }

  private function runner(FakeProcessRunner $fake, array $extraSshOptions = []): DrushRunner {
    return new DrushRunner($fake, self::CONTROL_DIR, $extraSshOptions);
  }

  public function testSshArgvIsExactAndUnshelled(): void {
    $fake = new FakeProcessRunner();
    $argv = $this->runner($fake)->sshCommandFor(
      $this->alias(),
      'https://abc.example.invalid',
      DrushCommandLine::fromString('sapi-i acquia_search_index')
    );

    $expectedRemote = "'/var/www/html/examplegroup.01live/vendor/bin/drush' "
      . "'--root=/var/www/html/examplegroup.01live/docroot' "
      . "'--uri=https://abc.example.invalid' 'sapi-i' 'acquia_search_index'";

    $this->assertSame([
      'ssh',
      '-n',
      '-o', 'BatchMode=yes',
      '-o', 'ConnectTimeout=15',
      '-o', 'ServerAliveInterval=30',
      '-o', 'ServerAliveCountMax=6',
      '-o', 'ControlMaster=no',
      '-o', 'ControlPath=' . self::CONTROL_DIR . '/' . substr(hash('sha256', 'examplegroup.01live@examplegroup01live.ssh.example-realm.example.invalid'), 0, 16),
      'examplegroup.01live@examplegroup01live.ssh.example-realm.example.invalid',
      $expectedRemote,
    ], $argv);
  }

  public function testRemoteCommandIsOneOpaqueArgument(): void {
    // The whole remote command must be a single argv entry, so Process's
    // array form hands it to ssh unsplit and no local shell ever sees it.
    $argv = $this->runner(new FakeProcessRunner())->sshCommandFor(
      $this->alias(),
      'https://abc.example.invalid',
      DrushCommandLine::fromString('sapi-i')
    );
    $this->assertStringStartsWith("'", (string) end($argv));
  }

  public function testInjectionAttemptStaysInsideQuotes(): void {
    $argv = $this->runner(new FakeProcessRunner())->sshCommandFor(
      $this->alias(),
      'https://abc.example.invalid',
      DrushCommandLine::fromString('state:set evil "a; id $(whoami) `id`"')
    );

    $remote = (string) end($argv);
    $this->assertStringContainsString("'a; id \$(whoami) `id`'", $remote);
    // Nothing escaped its quoting: no bare metacharacter between tokens.
    $this->assertDoesNotMatchRegularExpression('/(^|\s);\s/', $remote);
  }

  public function testControlPathIsHashedAndShort(): void {
    $path = $this->runner(new FakeProcessRunner())->controlPathFor($this->alias());

    $this->assertMatchesRegularExpression('#/[0-9a-f]{16}$#', $path);
    $this->assertLessThan(104, strlen($path), 'ControlPath must fit the AF_UNIX sun_path limit.');
  }

  public function testDifferentAliasesUseDifferentControlPaths(): void {
    $runner = $this->runner(new FakeProcessRunner());

    $wildcard = $this->alias('per-site-group', 'other');
    $perSite = $this->alias('per-site-group', 'abc');

    $this->assertNotSame($wildcard->host, $perSite->host);
    $this->assertNotSame($runner->controlPathFor($wildcard), $runner->controlPathFor($perSite));
    $this->assertSame($runner->controlPathFor($perSite), $runner->controlPathFor($perSite));
  }

  public function testStepsRunAsClientsNeverAsMasters(): void {
    $fake = new FakeProcessRunner();
    $this->runner($fake)->runSequence(
      $this->site(), $this->alias(), 'https://abc.example.invalid',
      $this->commands(['sapi-rt', 'sapi-i']), 1800
    );

    foreach ($fake->argvsOfKind('step') as $argv) {
      $this->assertContains('ControlMaster=no', $argv);
      $this->assertContains('-n', $argv);
      $this->assertNotContains('-M', $argv);
      $this->assertNotContains('-f', $argv);
    }
  }

  public function testPreflightOpensMasterWithoutRunningACommand(): void {
    $fake = new FakeProcessRunner();
    $runner = $this->runner($fake);
    $runner->preflight($this->alias(), 30);

    $this->assertSame(1, $fake->countOfKind('master'));
    $this->assertSame(1, $fake->countOfKind('probe'));

    $master = $fake->argvsOfKind('master')[0];
    foreach (['-f', '-N', '-M'] as $flag) {
      $this->assertContains($flag, $master);
    }
    // No remote command: the last element is the destination.
    $this->assertSame('examplegroup.01live@examplegroup01live.ssh.example-realm.example.invalid', end($master));
  }

  public function testPreflightOpensTheMasterOncePerHost(): void {
    $fake = new FakeProcessRunner();
    $runner = $this->runner($fake);
    $runner->preflight($this->alias(), 30);
    $runner->preflight($this->alias(), 30);

    $this->assertSame(1, $fake->countOfKind('master'), 'The master is reused, not reopened.');
    $this->assertSame(2, $fake->countOfKind('probe'));
  }

  public function testPreflightProbeRunsDrushVersionWithoutRootOrUri(): void {
    $fake = new FakeProcessRunner();
    $this->runner($fake)->preflight($this->alias(), 30);

    $remote = (string) end($fake->argvsOfKind('probe')[0]);
    $this->assertSame("'/var/www/html/examplegroup.01live/vendor/bin/drush' '--version'", $remote);
  }

  public function testFailureSkipsRemainingStepsOfThatSite(): void {
    $fake = new FakeProcessRunner([new ProcessOutcome(1, FALSE, 'boom')]);
    $result = $this->runner($fake)->runSequence(
      $this->site(), $this->alias(), 'https://abc.example.invalid',
      $this->commands(['sapi-sis acquia_search_index searchstax', 'sapi-rt', 'sapi-i']), 1800
    );

    $this->assertSame(
      [StepStatus::Failed, StepStatus::Skipped, StepStatus::Skipped],
      array_map(static fn ($s) => $s->status, $result->steps)
    );
    $this->assertSame(1, $fake->countOfKind('step'), 'Only the failing step should have run.');
    $this->assertTrue($result->failed());
    $this->assertSame('boom', $result->steps[0]->error);
  }

  public function testContinueOnErrorRunsEveryStep(): void {
    $fake = new FakeProcessRunner([new ProcessOutcome(1, FALSE, 'boom')]);
    $result = $this->runner($fake)->runSequence(
      $this->site(), $this->alias(), 'https://abc.example.invalid',
      $this->commands(['sapi-rt', 'sapi-i', 'cr']), 1800, TRUE
    );

    $this->assertSame(3, $fake->countOfKind('step'));
    $this->assertSame(
      [StepStatus::Failed, StepStatus::Ok, StepStatus::Ok],
      array_map(static fn ($s) => $s->status, $result->steps)
    );
  }

  public function testTimeoutIsReportedAsTimedOutAndAbortsTheCascade(): void {
    $fake = new FakeProcessRunner([new ProcessOutcome(NULL, TRUE)]);
    $result = $this->runner($fake)->runSequence(
      $this->site(), $this->alias(), 'https://abc.example.invalid',
      $this->commands(['sapi-i', 'cr']), 60
    );

    $this->assertSame(StepStatus::TimedOut, $result->steps[0]->status);
    $this->assertSame('Timed out.', $result->steps[0]->error);
    $this->assertSame(StepStatus::Skipped, $result->steps[1]->status);
    $this->assertSame(1, $fake->countOfKind('step'));
  }

  public function testFailureCapturesStdoutWhenStderrIsEmpty(): void {
    // Drush does not reliably use stderr; without this fallback the Error
    // column would be blank for exactly these failures.
    $fake = new FakeProcessRunner([new ProcessOutcome(1, FALSE, '', 'index not found')]);
    $result = $this->runner($fake)->runSequence(
      $this->site(), $this->alias(), 'https://abc.example.invalid',
      $this->commands(['sapi-i']), 1800
    );

    $this->assertSame('index not found', $result->steps[0]->error);
  }

  public function testSuccessfulStepHasNoError(): void {
    $fake = new FakeProcessRunner();
    $result = $this->runner($fake)->runSequence(
      $this->site(), $this->alias(), 'https://abc.example.invalid',
      $this->commands(['sapi-i']), 1800
    );

    $this->assertNull($result->steps[0]->error);
    $this->assertFalse($result->failed());
    $this->assertSame(0, $result->steps[0]->exitCode);
  }

  public function testDurationsAreRecordedPerStepAndZeroForSkipped(): void {
    $fake = new FakeProcessRunner([new ProcessOutcome(1, FALSE, 'boom')]);
    $result = $this->runner($fake)->runSequence(
      $this->site(), $this->alias(), 'https://abc.example.invalid',
      $this->commands(['sapi-i', 'cr']), 1800
    );

    $this->assertGreaterThan(0.0, $result->steps[0]->durationSeconds);
    $this->assertSame(0.0, $result->steps[1]->durationSeconds);
  }

  public function testOutputIsStreamedToTheCallback(): void {
    $fake = new FakeProcessRunner([], "indexing…\n");
    $seen = [];
    $this->runner($fake)->runSequence(
      $this->site(), $this->alias(), 'https://abc.example.invalid',
      $this->commands(['sapi-i']), 1800, FALSE,
      static function (string $type, string $chunk) use (&$seen): void {
        $seen[] = [$type, $chunk];
      }
    );

    $this->assertSame([['out', "indexing…\n"]], $seen);
  }

  public function testOnStepIsCalledForEveryStepIncludingSkipped(): void {
    $fake = new FakeProcessRunner([new ProcessOutcome(1, FALSE, 'boom')]);
    $indexes = [];
    $this->runner($fake)->runSequence(
      $this->site(), $this->alias(), 'https://abc.example.invalid',
      $this->commands(['a-cmd', 'b-cmd', 'c-cmd']), 1800, FALSE, NULL,
      static function (int $index) use (&$indexes): void {
        $indexes[] = $index;
      }
    );

    $this->assertSame([0, 1, 2], $indexes);
  }

  public function testDisconnectAllClosesEachOpenedMaster(): void {
    $fake = new FakeProcessRunner();
    $runner = $this->runner($fake);
    $runner->preflight($this->alias('per-site-group', 'abc'), 30);
    $runner->preflight($this->alias('per-site-group', 'other'), 30);
    $runner->disconnectAll();

    $this->assertSame(2, $fake->countOfKind('master'));
    $this->assertSame(2, $fake->countOfKind('disconnect'));
    foreach ($fake->argvsOfKind('disconnect') as $argv) {
      $this->assertContains('-O', $argv);
      $this->assertContains('exit', $argv);
    }
  }

  public function testDisconnectAllIsIdempotent(): void {
    $fake = new FakeProcessRunner();
    $runner = $this->runner($fake);
    $runner->preflight($this->alias(), 30);
    $runner->disconnectAll();
    $runner->disconnectAll();

    $this->assertSame(1, $fake->countOfKind('disconnect'));
  }

  public function testExtraSshOptionsArePassedThroughLast(): void {
    $fake = new FakeProcessRunner();
    $argv = $this->runner($fake, ['StrictHostKeyChecking=accept-new'])
      ->sshCommandFor($this->alias(), 'https://abc.example.invalid', DrushCommandLine::fromString('cr'));

    $optionValues = [];
    foreach ($argv as $i => $value) {
      if ($value === '-o') {
        $optionValues[] = $argv[$i + 1];
      }
    }
    $this->assertSame('StrictHostKeyChecking=accept-new', end($optionValues));
  }

  public function testTimeoutIsPassedToTheProcessRunner(): void {
    $fake = new FakeProcessRunner();
    $this->runner($fake)->runSequence(
      $this->site(), $this->alias(), 'https://abc.example.invalid',
      $this->commands(['sapi-i']), 42
    );

    $this->assertSame(42, $fake->invocations[0]['timeout']);
  }

}

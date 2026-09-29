<?php

declare(strict_types=1);

namespace Ecms\Deployment\Tests\Command;

use Ecms\Deployment\Command\DrushRunCommand;
use Ecms\Deployment\Config\FactoryEnvironment;
use Ecms\Deployment\Remote\ProcessOutcome;
use Ecms\Deployment\Tests\MockClientTrait;
use Ecms\Deployment\Tests\Remote\FakeProcessRunner;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * No tearDown() is declared here on purpose. MockClientTrait defines one,
 * and a class-level method would silently override it — the trait's cleanup
 * is not reachable via parent::tearDown() because the parent is TestCase.
 * Fake credentials would then leak into FactoryConfigTest.
 */
final class DrushRunCommandTest extends TestCase {

  use MockClientTrait;

  private const FIXTURES = __DIR__ . '/../fixtures/drush-sites/valid';

  private const FIXTURES_ROOT = __DIR__ . '/../fixtures/drush-sites';

  private function tester(MockHandler $mock, FakeProcessRunner $runner): CommandTester {
    return new CommandTester(new DrushRunCommand($this->mockedClient($mock, FactoryEnvironment::Prod), $runner));
  }

  /**
   * A GET sites/{id} response for one site.
   */
  private function siteResponse(int $id = 1, string $name = 'abc'): Response {
    return new Response(200, [], (string) json_encode([
      'id' => $id,
      'domain' => $name . '.example.invalid',
      'site' => $name,
    ]));
  }

  /**
   * The ssh control directories currently in the temp directory.
   *
   * @return string[]
   *   The directory paths.
   */
  private function controlDirectories(): array {
    return glob(sys_get_temp_dir() . '/ecms-ssh-*', GLOB_ONLYDIR) ?: [];
  }

  /**
   * The options every run needs.
   */
  private function options(array $extra = []): array {
    return array_merge([
      '--prod' => TRUE,
      '--sites' => '1',
      '--drush-sites-dir' => self::FIXTURES,
      '--cmd' => ['sapi-i acquia_search_index'],
      '--force' => TRUE,
      '--i-know-this-is-production' => TRUE,
    ], $extra);
  }

  public function testMissingCmdIsRefusedWithNoHttpAndNoSsh(): void {
    $runner = new FakeProcessRunner();
    $tester = $this->tester(new MockHandler([]), $runner);

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('--cmd is required');
    try {
      $tester->execute(['--sites' => '1', '--drush-sites-dir' => self::FIXTURES], ['interactive' => FALSE]);
    }
    finally {
      $this->assertSame([], $this->recordedMethods(), 'Validation must precede every API request.');
      $this->assertSame([], $runner->invocations, 'Validation must precede every SSH connection.');
    }
  }

  public function testInvalidCmdIsRefusedBeforeAnyApiCall(): void {
    $runner = new FakeProcessRunner();
    $tester = $this->tester(new MockHandler([]), $runner);

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('#Invalid drush command#');
    try {
      $tester->execute($this->options(['--cmd' => ['; id']]), ['interactive' => FALSE]);
    }
    finally {
      $this->assertSame([], $this->recordedMethods());
      $this->assertSame([], $runner->invocations);
    }
  }

  public function testInvalidTimeoutIsRefusedBeforeAnySsh(): void {
    $runner = new FakeProcessRunner();
    $tester = $this->tester(new MockHandler([]), $runner);

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('#--timeout must be a positive integer#');
    try {
      $tester->execute($this->options(['--timeout' => '0']), ['interactive' => FALSE]);
    }
    finally {
      $this->assertSame([], $runner->invocations);
    }
  }

  public function testMissingAliasFileIsRefusedBeforeAnyApiCall(): void {
    $runner = new FakeProcessRunner();
    $tester = $this->tester(new MockHandler([]), $runner);

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('#No drush site alias found#');
    try {
      $tester->execute($this->options(['--drush-sites-dir' => '/tmp/ecms-no-such-dir']), ['interactive' => FALSE]);
    }
    finally {
      $this->assertSame([], $this->recordedMethods(), 'The alias check runs ahead of site resolution.');
      $this->assertSame([], $runner->invocations);
    }
  }

  public function testProdWithoutConfirmationFlagRunsNothing(): void {
    $runner = new FakeProcessRunner();
    $tester = $this->tester(new MockHandler([]), $runner);

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('--i-know-this-is-production');
    try {
      $options = $this->options();
      unset($options['--i-know-this-is-production']);
      $tester->execute($options, ['interactive' => FALSE]);
    }
    finally {
      $this->assertSame([], $runner->invocations);
    }
  }

  public function testWrongEnvironmentAliasFileIsRefusedBeforeAnyApiCall(): void {
    $runner = new FakeProcessRunner();
    $tester = $this->tester(new MockHandler([]), $runner);

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('#declares ac-env "01test" but the target is "01live"#');
    try {
      $tester->execute($this->options(['--drush-sites-dir' => self::FIXTURES_ROOT . '/wrong-ac-env']), ['interactive' => FALSE]);
    }
    finally {
      $this->assertSame([], $this->recordedMethods(), 'The ac-env check runs ahead of site resolution.');
      $this->assertSame([], $runner->invocations);
    }
  }

  public function testNonInteractiveWithoutForceIsRefused(): void {
    // The confirm gate sits AFTER resolveSites() so the prompt can name real
    // sites, so read-only GETs before the refusal are expected and harmless.
    // The MockHandler must therefore be primed, or Guzzle throws
    // "Mock queue is empty" and the test passes for the wrong reason.
    $runner = new FakeProcessRunner();
    $tester = $this->tester(new MockHandler([$this->siteResponse()]), $runner);

    $before = $this->controlDirectories();

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('#without --force#');
    try {
      $options = $this->options();
      unset($options['--force']);
      $tester->execute($options, ['interactive' => FALSE]);
    }
    finally {
      $this->assertSame(['GET'], $this->recordedMethods());
      $this->assertSame([], $runner->invocations, 'No SSH before the operator has confirmed.');
      $this->assertSame($before, $this->controlDirectories(), 'A refused run must not leave a control directory.');
    }
  }

  public function testDeclinedPromptLeavesNoControlDirectory(): void {
    $runner = new FakeProcessRunner();
    $tester = $this->tester(new MockHandler([$this->siteResponse()]), $runner);
    $before = $this->controlDirectories();

    $options = $this->options();
    unset($options['--force']);
    $tester->setInputs(['no']);
    $exit = $tester->execute($options, ['interactive' => TRUE]);

    $this->assertSame(Command::SUCCESS, $exit);
    $this->assertStringContainsString('Aborted; nothing was run.', $tester->getDisplay());
    $this->assertSame([], $runner->invocations);
    $this->assertSame($before, $this->controlDirectories(), 'A declined run must not leave a control directory.');
  }

  public function testDryRunResolvesSitesButOpensNoConnection(): void {
    $runner = new FakeProcessRunner();
    $tester = $this->tester(new MockHandler([$this->siteResponse()]), $runner);

    $options = $this->options(['--dry-run' => TRUE]);
    unset($options['--force'], $options['--i-know-this-is-production']);
    $exit = $tester->execute($options, ['interactive' => FALSE]);

    $this->assertSame(Command::SUCCESS, $exit);
    $this->assertSame(['GET'], $this->recordedMethods());
    $this->assertSame([], $runner->invocations, 'A dry run must not connect to anything.');
    $this->assertStringContainsString('01live.site.yml', $tester->getDisplay());
    $this->assertStringContainsString('Dry run', $tester->getDisplay());
  }

  public function testDryRunLeavesNoControlDirectory(): void {
    $runner = new FakeProcessRunner();
    $tester = $this->tester(new MockHandler([$this->siteResponse()]), $runner);
    $before = $this->controlDirectories();

    $options = $this->options(['--dry-run' => TRUE]);
    unset($options['--force'], $options['--i-know-this-is-production']);
    $tester->execute($options, ['interactive' => FALSE]);

    $this->assertSame($before, $this->controlDirectories(), 'A dry run must not leave a control directory.');
  }

  public function testControlDirectoryExistsForEveryConnectionAndIsRemovedAfter(): void {
    $runner = new FakeProcessRunner();
    $tester = $this->tester(new MockHandler([$this->siteResponse()]), $runner);
    $before = $this->controlDirectories();

    $exit = $tester->execute($this->options(), ['interactive' => FALSE]);

    $this->assertSame(Command::SUCCESS, $exit);
    $this->assertNotSame([], $runner->invocations);
    foreach ($runner->invocations as $invocation) {
      $this->assertTrue($invocation['controlDirectoryExists'], sprintf('The control directory must exist for the %s call.', $invocation['kind']));
    }
    $this->assertSame($before, $this->controlDirectories(), 'The control directory must be removed after the run.');
  }

  public function testDryRunPrintsTheExactSshCommand(): void {
    $runner = new FakeProcessRunner();
    $tester = $this->tester(new MockHandler([$this->siteResponse()]), $runner);

    $options = $this->options(['--dry-run' => TRUE]);
    unset($options['--force'], $options['--i-know-this-is-production']);
    $tester->execute($options, ['interactive' => FALSE]);

    $display = preg_replace('/\s+/', ' ', $tester->getDisplay());
    $this->assertStringContainsString('ssh -n -o BatchMode=yes', $display);
    $this->assertStringContainsString("'--uri=https://abc.example.invalid'", $display);
  }

  public function testHappyPathRunsEveryStepAndSucceeds(): void {
    $runner = new FakeProcessRunner();
    $tester = $this->tester(new MockHandler([$this->siteResponse()]), $runner);

    $exit = $tester->execute(
      $this->options(['--cmd' => ['sapi-rt acquia_search_index', 'sapi-i acquia_search_index']]),
      ['interactive' => FALSE]
    );

    $this->assertSame(Command::SUCCESS, $exit);
    // 1 master + 1 probe + 2 steps + 1 disconnect.
    $this->assertSame(1, $runner->countOfKind('master'));
    $this->assertSame(1, $runner->countOfKind('probe'));
    $this->assertSame(2, $runner->countOfKind('step'));
    $this->assertSame(1, $runner->countOfKind('disconnect'));
    $this->assertStringContainsString('All 1 site(s) completed.', $tester->getDisplay());
  }

  public function testFailingStepSkipsTheRestAndExitsFailure(): void {
    $runner = new FakeProcessRunner([new ProcessOutcome(1, FALSE, 'no such index')]);
    $tester = $this->tester(new MockHandler([$this->siteResponse()]), $runner);

    $exit = $tester->execute(
      $this->options(['--cmd' => ['sapi-sis acquia_search_index searchstax', 'sapi-i acquia_search_index']]),
      ['interactive' => FALSE]
    );

    $this->assertSame(Command::FAILURE, $exit);
    $this->assertSame(1, $runner->countOfKind('step'), 'The second step must be skipped.');
    $display = $tester->getDisplay();
    $this->assertStringContainsString('SKIPPED', $display);
    $this->assertStringContainsString('no such index', $display);
  }

  public function testPreflightFailureAbortsBeforeAnyStep(): void {
    // A probe that fails means the SSH key, host key or drush path is wrong;
    // that must surface once, not once per site.
    $runner = new FakeProcessRunner([], '', new ProcessOutcome(127, FALSE, 'drush: not found'));
    $tester = $this->tester(new MockHandler([$this->siteResponse()]), $runner);

    $exit = $tester->execute($this->options(), ['interactive' => FALSE]);

    $this->assertSame(Command::FAILURE, $exit);
    $this->assertSame(0, $runner->countOfKind('step'), 'No step may run after a failed preflight.');
    $this->assertStringContainsString('Cannot run drush on', $tester->getDisplay());
  }

  public function testEmptySiteListWarnsAndSucceedsWithoutSsh(): void {
    $runner = new FakeProcessRunner();
    // --sites=all with an empty factory: one page, no sites, no next link.
    $tester = $this->tester(new MockHandler([new Response(200, [], (string) json_encode(['sites' => []]))]), $runner);

    $exit = $tester->execute($this->options(['--sites' => 'all']), ['interactive' => FALSE]);

    $this->assertSame(Command::SUCCESS, $exit);
    $this->assertSame([], $runner->invocations, 'Nothing to do must not open a connection.');
    $this->assertStringContainsString('No sites matched', $tester->getDisplay());
  }

  public function testSiteWithNullSiteNameFallsBackToItsFactoryDomain(): void {
    $runner = new FakeProcessRunner();
    $response = new Response(200, [], (string) json_encode([
      'id' => 1,
      'domain' => 'abc.example.invalid',
    ]));
    $tester = $this->tester(new MockHandler([$response]), $runner);

    $exit = $tester->execute($this->options(), ['interactive' => FALSE]);

    $this->assertSame(Command::SUCCESS, $exit);
    $this->assertSame(1, $runner->countOfKind('step'));
    $this->assertStringContainsString("'--uri=https://abc.example.invalid'", implode(' ', $runner->argvsOfKind('step')[0]));
  }

  public function testSiteWithNoUsableNameIsFailedWithoutSsh(): void {
    $runner = new FakeProcessRunner();
    // A custom domain outside the factory, and no "site" key.
    $response = new Response(200, [], (string) json_encode([
      'id' => 1,
      'domain' => 'abc.example.com',
    ]));
    $tester = $this->tester(new MockHandler([$response]), $runner);

    $exit = $tester->execute($this->options(), ['interactive' => FALSE]);

    $this->assertSame(Command::FAILURE, $exit);
    $this->assertSame(0, $runner->countOfKind('step'), 'A site with no trustworthy name must not be connected to.');
    $this->assertStringContainsString('Cannot resolve a site name', $tester->getDisplay());
  }

  public function testSiteWithNoMatchingGroupReportsTheAliasLoaderReason(): void {
    // The file has only "abc" and "zyx" groups and no "*" wildcard.
    $runner = new FakeProcessRunner();
    $tester = $this->tester(new MockHandler([$this->siteResponse(1, 'nosuchsite')]), $runner);

    // No --force: with no targetable site, the run must not reach the
    // confirmation gate, which would refuse a non-interactive run.
    $options = $this->options(['--drush-sites-dir' => self::FIXTURES_ROOT . '/named-groups-only']);
    unset($options['--force']);
    $exit = $tester->execute($options, ['interactive' => FALSE]);

    $this->assertSame(Command::FAILURE, $exit);
    $this->assertSame([], $runner->invocations, 'An unresolved site must not be connected to.');
    $display = preg_replace('/\s+/', ' ', $tester->getDisplay());
    $this->assertStringContainsString('Site 1 unresolved:', $display);
    $this->assertStringContainsString('defines no group for site "nosuchsite"', $display);
    $this->assertStringNotContainsString('Cannot resolve a site name', $display);
  }

  public function testAuditLineIsEmittedAtVerbose(): void {
    $runner = new FakeProcessRunner();
    $tester = $this->tester(new MockHandler([$this->siteResponse()]), $runner);

    $tester->execute($this->options(), ['interactive' => FALSE, 'verbosity' => \Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERBOSE]);

    $this->assertStringContainsString('ecms:drush:run env=prod site=abc site_id=1 step=1/1', $tester->getDisplay());
  }

  public function testSummaryLineCountsEveryStatus(): void {
    $runner = new FakeProcessRunner([new ProcessOutcome(1, FALSE, 'boom')]);
    $tester = $this->tester(new MockHandler([$this->siteResponse()]), $runner);

    $tester->execute(
      $this->options(['--cmd' => ['sapi-rt acquia_search_index', 'sapi-i acquia_search_index']]),
      ['interactive' => FALSE]
    );

    $this->assertStringContainsString('sites=1 steps=2 ok=0 failed=1 timed_out=0 skipped=1', $tester->getDisplay());
  }

  public function testLogFileIsWrittenWithoutMarkup(): void {
    $log = sys_get_temp_dir() . '/ecms-drush-run-test-' . bin2hex(random_bytes(4)) . '.log';
    $runner = new FakeProcessRunner();
    $tester = $this->tester(new MockHandler([$this->siteResponse()]), $runner);

    try {
      $tester->execute($this->options(['--log' => $log]), ['interactive' => FALSE]);

      $contents = (string) file_get_contents($log);
      $this->assertStringContainsString('Commands (1), in order:', $contents);
      $this->assertStringNotContainsString('<fg=', $contents, 'Console markup must be stripped from the log.');
      $this->assertStringNotContainsString("\e[", $contents, 'ANSI escapes must be stripped from the log.');
    }
    finally {
      @unlink($log);
    }
  }

  public function testDrushOutputReachesConsoleAndLogUnchanged(): void {
    $log = sys_get_temp_dir() . '/ecms-drush-run-test-' . bin2hex(random_bytes(4)) . '.log';
    $chunk = "Deleted <em class=\"placeholder\">abc</em>; a <b and c> d; <info>kept</info>\n";
    $runner = new FakeProcessRunner([], $chunk . "\e[32mgreen\e[0m\n");
    $tester = $this->tester(new MockHandler([$this->siteResponse()]), $runner);

    try {
      $tester->execute($this->options(['--log' => $log]), ['interactive' => FALSE]);

      $contents = (string) file_get_contents($log);
      $this->assertStringContainsString($chunk, $contents, 'Drush output must reach the log unchanged.');
      $this->assertStringContainsString("green\n", $contents);
      $this->assertStringNotContainsString("\e[", $contents, 'ANSI escapes must be stripped from the log.');
      $this->assertStringNotContainsString('<fg=', $contents, 'Console markup must be stripped from the log.');
      $this->assertStringContainsString($chunk, $tester->getDisplay(), 'Drush output must not be formatted on the console.');
    }
    finally {
      @unlink($log);
    }
  }

  public function testUnwritableLogDirectoryIsRefusedUpFront(): void {
    $runner = new FakeProcessRunner();
    $tester = $this->tester(new MockHandler([]), $runner);

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('#--log directory#');
    try {
      $tester->execute($this->options(['--log' => '/tmp/ecms-no-such-dir/run.log']), ['interactive' => FALSE]);
    }
    finally {
      $this->assertSame([], $runner->invocations);
    }
  }

}

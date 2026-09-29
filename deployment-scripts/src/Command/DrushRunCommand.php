<?php

declare(strict_types=1);

namespace Ecms\Deployment\Command;

use Ecms\Deployment\Api\AcsfClient;
use Ecms\Deployment\Api\Site;
use Ecms\Deployment\Config\FactoryEnvironment;
use Ecms\Deployment\Remote\DrushCommandLine;
use Ecms\Deployment\Remote\DrushRunner;
use Ecms\Deployment\Remote\ProcessRunnerInterface;
use Ecms\Deployment\Remote\SiteAliasLoader;
use Ecms\Deployment\Remote\SiteRunResult;
use Ecms\Deployment\Remote\StepResult;
use Ecms\Deployment\Remote\StepStatus;
use Ecms\Deployment\Remote\SymfonyProcessRunner;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Runs an ordered sequence of drush commands on ACSF sites over SSH.
 */
#[AsCommand(
  name: 'ecms:drush:run',
  aliases: ['ecms:drush'],
  description: 'Runs an ordered sequence of drush commands on one or more sites over SSH.',
  help: <<<'HELP'
Runs each --cmd, in order, on every selected site.

  <info>bin/ecms ecms:drush:run --env=test --sites=111 --cmd="cr"</info>

Repeat --cmd for a sequence. If a step fails, that site's remaining steps
are skipped (use --continue-on-error to override); other sites still run.
The command exits non-zero if any site failed.

Re-index every site for SearchStax:

  <info>bin/ecms ecms:drush:run --prod --sites=all \
    --cmd="sapi-sis acquia_search_index searchstax" \
    --cmd="sapi-rt acquia_search_index" \
    --cmd="sapi-i acquia_search_index" \
    --force --i-know-this-is-production --log=/tmp/reindex.log</info>

Always run with --dry-run first: it resolves every site and prints the exact
ssh command without connecting to anything.

<comment>Prerequisites.</comment> Unlike the other ecms commands this needs an Acquia SSH
key, and the drush site aliases at drush/sites/{01dev,01test,01live}.site.yml.
Download those from the Acquia Cloud UI (your user profile > Credentials).
They are gitignored and must never be committed — this project is open
source and they name SSH hosts and server paths.
HELP,
)]
final class DrushRunCommand extends AbstractAcsfCommand {

  /**
   * Refuse to operate on more sites than this in one run.
   */
  private const MAX_SITES = 200;

  /**
   * Warn above this many sites, with a worst-case runtime estimate.
   */
  private const LARGE_RUN_THRESHOLD = 25;

  /**
   * The production guardrail flag, mirroring BackupPruneCommand.
   */
  private const PRODUCTION_CONFIRMATION_FLAG = 'i-know-this-is-production';

  /**
   * The commands to run, parsed in initialize().
   *
   * @var \Ecms\Deployment\Remote\DrushCommandLine[]
   */
  private array $commands = [];

  /**
   * The per-step timeout in seconds, parsed in initialize().
   */
  private int $timeoutSeconds = 1800;

  /**
   * The alias loader, built in initialize().
   */
  private SiteAliasLoader $aliasLoader;

  /**
   * The open log file handle, or NULL when --log was not given.
   *
   * @var resource|null
   */
  private $logHandle = NULL;

  /**
   * Whether the last streamed output ended on a fresh line.
   */
  private bool $atLineStart = TRUE;

  /**
   * Whether a failing step should stop that site's remaining steps.
   */
  private bool $continueOnError = FALSE;

  /**
   * The 0700 directory holding this run's ssh control sockets.
   */
  private ?string $controlDirectory = NULL;

  /**
   * Constructs the command.
   *
   * @param \Ecms\Deployment\Api\AcsfClient|null $injectedClient
   *   Test-only seam, passed to the parent.
   * @param \Ecms\Deployment\Remote\ProcessRunnerInterface|null $injectedRunner
   *   Test-only seam: lets tests assert the exact ssh argv with no network.
   *   Production code never passes this.
   */
  public function __construct(
    ?AcsfClient $injectedClient = NULL,
    private readonly ?ProcessRunnerInterface $injectedRunner = NULL,
  ) {
    parent::__construct($injectedClient);
  }

  /**
   * {@inheritdoc}
   */
  protected function configure(): void {
    parent::configure();
    $this
      ->addOption('cmd', NULL, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'A drush command to run. Repeat for a sequence; order is preserved.')
      ->addOption('timeout', NULL, InputOption::VALUE_REQUIRED, 'Seconds allowed per step.', '1800')
      ->addOption('continue-on-error', NULL, InputOption::VALUE_NONE, "Run a site's remaining steps even after one fails.")
      ->addOption('dry-run', NULL, InputOption::VALUE_NONE, 'Resolve sites and print the plan without connecting to anything.')
      ->addOption('force', NULL, InputOption::VALUE_NONE, 'Skip the confirmation prompt. Required for non-interactive runs.')
      ->addOption(self::PRODUCTION_CONFIRMATION_FLAG, NULL, InputOption::VALUE_NONE, 'Required in addition to --force when targeting production (--prod).')
      ->addOption('drush-sites-dir', NULL, InputOption::VALUE_REQUIRED, 'Directory holding the {01dev,01test,01live}.site.yml aliases.')
      ->addOption('ssh-option', NULL, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Extra ssh -o option. Repeatable. Escape hatch; not normally needed.')
      ->addOption('log', NULL, InputOption::VALUE_REQUIRED, 'Tee all output to this file. Recommended for long production runs.');
  }

  /**
   * {@inheritdoc}
   *
   * Offline validation runs *before* parent::initialize(), which builds
   * FactoryConfig and throws when ACSF credentials are missing. Symfony
   * calls initialize() before execute(), so anything validated in execute()
   * would only be reached after the tool had already demanded credentials —
   * an operator with a malformed --cmd would be told about their API key
   * instead. Nothing here reads a credential, makes an HTTP request, or
   * opens a connection.
   */
  protected function initialize(InputInterface $input, OutputInterface $output): void {
    $this->commands = $this->parseCommands($input);
    $this->timeoutSeconds = $this->parseTimeout($input);
    $this->continueOnError = (bool) $input->getOption('continue-on-error');
    $this->openLog($input);

    $this->aliasLoader = new SiteAliasLoader($this->resolveSitesDirectory($input));
    $this->aliasLoader->assertUsable(FactoryEnvironment::fromInput($input));

    parent::initialize($input, $output);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $dryRun = (bool) $input->getOption('dry-run');
    $aliasPath = $this->aliasLoader->pathFor($this->environment);

    // Free flag check, so it needs no context and costs nothing.
    if (!$dryRun) {
      $this->requireExplicitProductionConfirmation($input, self::PRODUCTION_CONFIRMATION_FLAG);
    }

    $sites = $this->resolveSites($input);
    if ($sites === []) {
      $this->io->warning('No sites matched; nothing to do.');
      return Command::SUCCESS;
    }

    if (count($sites) > self::MAX_SITES) {
      throw new \InvalidArgumentException(sprintf(
        'Refusing to run against %d sites; the cap is %d. Split the run with --sites.',
        count($sites),
        self::MAX_SITES
      ));
    }

    $runner = new DrushRunner(
      $this->injectedRunner ?? new SymfonyProcessRunner(),
      $this->controlPathDirectory(),
      $input->getOption('ssh-option')
    );

    // Resolve every site up front so the plan, and the confirmation, show
    // real names rather than a bare count.
    $resolved = [];
    $unresolved = [];
    foreach ($sites as $site) {
      try {
        $resolved[] = $this->resolveTarget($site);
      }
      catch (\InvalidArgumentException $e) {
        $unresolved[] = ['site' => $site, 'reason' => $e->getMessage()];
      }
    }

    $this->printPlan($output, $aliasPath, $resolved, $unresolved);

    if ($dryRun) {
      $this->printDryRunCommand($output, $runner, $resolved);
      $this->io->note('Dry run: nothing was connected to.');
      return Command::SUCCESS;
    }

    // With no targetable site there is nothing to confirm.
    if ($resolved !== [] && !$this->confirm($input, count($resolved))) {
      $this->io->warning('Aborted; nothing was run.');
      return Command::SUCCESS;
    }

    $results = [];
    foreach ($unresolved as $entry) {
      $results[] = SiteRunResult::unresolved($entry['site'], $entry['reason'], $this->commands);
    }

    try {
      // Created only now, so a dry run, a declined prompt or a refusal
      // leaves nothing behind in the temp directory.
      $this->createControlDirectory();
      if ($resolved !== [] && !$this->preflight($runner, $resolved)) {
        return Command::FAILURE;
      }
      $results = [...$results, ...$this->runAll($output, $runner, $resolved)];
    }
    finally {
      $runner->disconnectAll();
      $this->cleanUpControlDirectory();
      $this->closeLog();
    }

    return $this->report($results);
  }

  /**
   * Parses and validates every --cmd, with no HTTP and no SSH.
   *
   * @return \Ecms\Deployment\Remote\DrushCommandLine[]
   *   The commands, in the order given.
   */
  private function parseCommands(InputInterface $input): array {
    $raw = (array) $input->getOption('cmd');
    if ($raw === []) {
      throw new \InvalidArgumentException('--cmd is required. Give at least one, e.g. --cmd="sapi-i".');
    }

    return array_map(
      static fn (string $value): DrushCommandLine => DrushCommandLine::fromString($value),
      $raw
    );
  }

  /**
   * Parses and validates --timeout.
   */
  private function parseTimeout(InputInterface $input): int {
    $timeout = filter_var($input->getOption('timeout'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($timeout === FALSE) {
      throw new \InvalidArgumentException(sprintf(
        '--timeout must be a positive integer number of seconds, got "%s".',
        (string) $input->getOption('timeout')
      ));
    }
    return $timeout;
  }

  /**
   * The directory holding the alias files.
   */
  private function resolveSitesDirectory(InputInterface $input): string {
    $option = $input->getOption('drush-sites-dir');
    if (is_string($option) && $option !== '') {
      return $option;
    }
    return dirname(__DIR__, 3) . '/drush/sites';
  }

  /**
   * Resolves one site's alias and uri.
   *
   * @return array{site: Site, alias: SiteAlias, uri: string}
   *   The resolved target.
   *
   * @throws \InvalidArgumentException
   *   If the site cannot be targeted. The message is the operator-facing
   *   reason, taken from the alias loader where it has one.
   */
  private function resolveTarget(Site $site): array {
    $name = $this->siteNameFor($site);
    if ($name === NULL) {
      throw new \InvalidArgumentException($this->unresolvedReason($site));
    }

    $alias = $this->aliasLoader->load($this->environment, $name);
    return ['site' => $site, 'alias' => $alias, 'uri' => $alias->uriFor($name)];
  }

  /**
   * The machine name to use for a site, or NULL if none can be trusted.
   *
   * Site::$siteName is nullable, so fall back to the domain — but only when
   * the domain already sits inside this factory's own domain space, never a
   * custom domain that may be proxied or unattached.
   */
  private function siteNameFor(Site $site): ?string {
    if ($site->siteName !== NULL && $site->siteName !== '') {
      return $site->siteName;
    }

    try {
      return $this->aliasLoader->load($this->environment)->siteNameFromDomain($site->domain);
    }
    catch (\InvalidArgumentException) {
      return NULL;
    }
  }

  /**
   * The operator-facing reason a site could not be targeted.
   */
  private function unresolvedReason(Site $site): string {
    return sprintf(
      'Cannot resolve a site name: the ACSF API returned no "site" for site %d and its domain "%s" is outside this factory.',
      $site->id,
      $site->domain
    );
  }

  /**
   * Prints the resolved plan before anything connects.
   *
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The console output.
   * @param string $aliasPath
   *   The alias file this run resolved its connections from.
   * @param array<int, array{site: Site, alias: \Ecms\Deployment\Remote\SiteAlias, uri: string}> $resolved
   *   The targetable sites.
   * @param array<int, array{site: Site, reason: string}> $unresolved
   *   Sites that will be failed without an SSH attempt, with the reason.
   */
  private function printPlan(OutputInterface $output, string $aliasPath, array $resolved, array $unresolved): void {
    $this->write($output, sprintf("Site alias: %s\n", $aliasPath));

    $this->write($output, sprintf("\nCommands (%d), in order:\n", count($this->commands)));
    foreach ($this->commands as $index => $command) {
      $this->write($output, sprintf("  %d. %s\n", $index + 1, $command->raw));
    }
    $this->write($output, "\n");

    $rows = [];
    foreach ($resolved as $target) {
      $rows[] = [$target['site']->id, $target['site']->siteName ?? '—', $target['uri']];
    }
    foreach ($unresolved as $entry) {
      $rows[] = [$entry['site']->id, $entry['site']->siteName ?? '—', '<fg=red>unresolved</>'];
    }
    $this->io->table(['Site ID', 'Site name', 'URI'], $rows);

    foreach ($unresolved as $entry) {
      $this->write($output, sprintf("Site %d unresolved: %s\n", $entry['site']->id, $entry['reason']), raw: TRUE);
    }

    $hosts = array_unique(array_map(static fn (array $t): string => $t['alias']->userAtHost(), $resolved));
    if (count($hosts) > 1) {
      $this->io->note(sprintf('Sites resolve to %d different hosts; multiple stacks are in play.', count($hosts)));
    }

    if (count($resolved) > self::LARGE_RUN_THRESHOLD) {
      $this->io->warning(sprintf(
        'Large run: %d sites x %d command(s). Worst case %s at the %ds timeout. Consider --log.',
        count($resolved),
        count($this->commands),
        $this->humanDuration(count($resolved) * count($this->commands) * $this->timeoutSeconds),
        $this->timeoutSeconds
      ));
    }
  }

  /**
   * Prints the exact ssh argv for the first site during a dry run.
   *
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The console output.
   * @param \Ecms\Deployment\Remote\DrushRunner $runner
   *   The runner, used only to build the argv.
   * @param array<int, array{site: Site, alias: \Ecms\Deployment\Remote\SiteAlias, uri: string}> $resolved
   *   The targetable sites.
   */
  private function printDryRunCommand(OutputInterface $output, DrushRunner $runner, array $resolved): void {
    if ($resolved === []) {
      return;
    }

    $first = $resolved[0];
    $this->write($output, "First site's first step would run:\n\n");
    $argv = $runner->sshCommandFor($first['alias'], $first['uri'], $this->commands[0]);
    $this->write($output, '  ' . implode(' ', $argv) . "\n\n");
  }

  /**
   * The force/confirm gate, run once the operator can see the plan.
   */
  private function confirm(InputInterface $input, int $siteCount): bool {
    if ($input->getOption('force')) {
      return TRUE;
    }

    if (!$input->isInteractive()) {
      throw new \InvalidArgumentException('Refusing to run drush commands in a non-interactive run without --force.');
    }

    return $this->io->confirm(sprintf(
      'Run %d command(s) on %d site(s) in %s (%s)?',
      count($this->commands),
      $siteCount,
      $this->environment->value,
      $this->environment->acquiaEnvironment()
    ), FALSE);
  }

  /**
   * Opens a multiplexed master per distinct host and probes remote drush.
   *
   * @param \Ecms\Deployment\Remote\DrushRunner $runner
   *   The runner that owns the connections.
   * @param array<int, array{site: Site, alias: \Ecms\Deployment\Remote\SiteAlias, uri: string}> $resolved
   *   The targetable sites.
   *
   * @return bool
   *   FALSE if any host failed its probe, so the run should stop.
   */
  private function preflight(DrushRunner $runner, array $resolved): bool {
    $seen = [];
    foreach ($resolved as $target) {
      $key = $target['alias']->userAtHost();
      if (isset($seen[$key])) {
        continue;
      }
      $seen[$key] = TRUE;

      $outcome = $runner->preflight($target['alias'], min(60, $this->timeoutSeconds));
      if (!$outcome->succeeded()) {
        $this->io->error(sprintf(
          "Cannot run drush on %s.\n%s",
          $key,
          trim($outcome->errorOutput) ?: trim($outcome->output) ?: 'No output; check your SSH key and the alias host.'
        ));
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Runs every site in turn, streaming output.
   *
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The console output.
   * @param \Ecms\Deployment\Remote\DrushRunner $runner
   *   The runner that executes each step.
   * @param array<int, array{site: Site, alias: \Ecms\Deployment\Remote\SiteAlias, uri: string}> $resolved
   *   The targetable sites.
   *
   * @return \Ecms\Deployment\Remote\SiteRunResult[]
   *   One result per site.
   */
  private function runAll(OutputInterface $output, DrushRunner $runner, array $resolved): array {
    $results = [];
    $total = count($resolved);

    foreach ($resolved as $index => $target) {
      $site = $target['site'];
      $this->write($output, sprintf(
        "\n[%d/%d] %s (site %d) — %s\n",
        $index + 1,
        $total,
        $site->siteName ?? $site->domain,
        $site->id,
        $target['uri']
      ));

      $stepCount = count($this->commands);
      $results[] = $runner->runSequence(
        $site,
        $target['alias'],
        $target['uri'],
        $this->commands,
        $this->timeoutSeconds,
        $this->continueOnError,
        fn (string $type, string $chunk) => $this->stream($output, $chunk),
        function (int $i, StepResult $result) use ($output, $stepCount, $site): void {
          $this->ensureLineStart($output);
          $this->write($output, sprintf(
            "  %d/%d %s … %s (%.1fs)\n",
            $i + 1,
            $stepCount,
            $result->command->raw,
            $result->status->formatted(),
            $result->durationSeconds
          ));
          $this->audit($output, $site, $i + 1, $stepCount, $result);
        }
      );
    }

    return $results;
  }

  /**
   * Prints the results table and the run summary.
   *
   * @param \Ecms\Deployment\Remote\SiteRunResult[] $results
   *   Every site's result.
   */
  private function report(array $results): int {
    $rows = [];
    $counts = [
      StepStatus::Ok->value => 0,
      StepStatus::Failed->value => 0,
      StepStatus::TimedOut->value => 0,
      StepStatus::Skipped->value => 0,
    ];

    foreach ($results as $result) {
      foreach ($result->steps as $step) {
        $counts[$step->status->value]++;
        $rows[] = [
          $result->site->siteName ?? $result->site->domain,
          $result->site->id,
          $step->command->raw,
          $step->status->formatted(),
          $step->status === StepStatus::Skipped ? '—' : sprintf('%.1fs', $step->durationSeconds),
          $this->oneLine($step->error),
        ];
      }
    }

    $this->io->table(['Site', 'Site ID', 'Step', 'Result', 'Duration', 'Error'], $rows);

    $failedSites = array_filter($results, static fn (SiteRunResult $r): bool => $r->failed());
    $this->io->writeln(sprintf(
      'ecms:drush:run env=%s sites=%d steps=%d ok=%d failed=%d timed_out=%d skipped=%d',
      $this->environment->value,
      count($results),
      count($this->commands),
      $counts[StepStatus::Ok->value],
      $counts[StepStatus::Failed->value],
      $counts[StepStatus::TimedOut->value],
      $counts[StepStatus::Skipped->value]
    ));

    if ($failedSites !== []) {
      $this->io->error(sprintf('%d of %d site(s) failed.', count($failedSites), count($results)));
      return Command::FAILURE;
    }

    $this->io->success(sprintf('All %d site(s) completed.', count($results)));
    return Command::SUCCESS;
  }

  /**
   * Emits the machine-greppable per-step audit line.
   */
  private function audit(OutputInterface $output, Site $site, int $step, int $total, StepResult $result): void {
    $this->write($output, sprintf(
      "ecms:drush:run env=%s site=%s site_id=%d step=%d/%d cmd=\"%s\" status=%s exit=%s duration=%.1f\n",
      $this->environment->value,
      $site->siteName ?? $site->domain,
      $site->id,
      $step,
      $total,
      $result->command->raw,
      $result->status->value,
      $result->exitCode ?? '-',
      $result->durationSeconds
    ), OutputInterface::VERBOSITY_VERBOSE);
  }

  /**
   * Streams a raw process chunk, tracking whether it ended mid-line.
   */
  private function stream(OutputInterface $output, string $chunk): void {
    if ($chunk === '') {
      return;
    }
    $this->write($output, $chunk, raw: TRUE);
    $this->atLineStart = str_ends_with($chunk, "\n");
  }

  /**
   * Emits a newline if the last streamed chunk left the cursor mid-line.
   */
  private function ensureLineStart(OutputInterface $output): void {
    if (!$this->atLineStart) {
      $this->write($output, "\n");
    }
  }

  /**
   * Writes to the console and, when --log is set, to the log file.
   *
   * Console colour tags are stripped before anything reaches disk, so the
   * log stays greppable plain text. Raw text, such as drush output, is
   * written to the console unformatted and to the log with only ANSI
   * escapes removed, so markup-like text in it survives.
   */
  private function write(OutputInterface $output, string $text, int $verbosity = OutputInterface::VERBOSITY_NORMAL, bool $raw = FALSE): void {
    if (!$output->isQuiet()) {
      $output->write($text, FALSE, $verbosity | ($raw ? OutputInterface::OUTPUT_RAW : OutputInterface::OUTPUT_NORMAL));
    }
    if ($this->logHandle !== NULL) {
      fwrite($this->logHandle, $raw ? $this->stripAnsi($text) : $this->stripFormatting($text));
    }
    if ($text !== '') {
      $this->atLineStart = str_ends_with($text, "\n");
    }
  }

  /**
   * Removes console style tags and ANSI escapes for the log file.
   *
   * Only Symfony console style tags are removed, so other text in angle
   * brackets, such as a command's own arguments, is kept.
   */
  private function stripFormatting(string $text): string {
    $text = (string) preg_replace('#</?(?:info|comment|error|question|(?:fg|bg|options|href)=[^<>]*)?>#', '', $text);
    return $this->stripAnsi($text);
  }

  /**
   * Removes ANSI colour escapes.
   */
  private function stripAnsi(string $text): string {
    return (string) preg_replace('/\e\[[0-9;]*m/', '', $text);
  }

  /**
   * Collapses an error to a single line for the results table.
   */
  private function oneLine(?string $error): string {
    if ($error === NULL || $error === '') {
      return '';
    }
    $line = trim((string) preg_replace('/\s+/', ' ', $error));
    return mb_strlen($line) > 120 ? mb_substr($line, 0, 117) . '…' : $line;
  }

  /**
   * Opens --log, failing now rather than at the end of a long run.
   */
  private function openLog(InputInterface $input): void {
    $path = $input->getOption('log');
    if (!is_string($path) || $path === '') {
      return;
    }

    $directory = dirname($path);
    if (!is_dir($directory) || !is_writable($directory)) {
      throw new \InvalidArgumentException(sprintf('--log directory "%s" does not exist or is not writable.', $directory));
    }

    $handle = fopen($path, 'ab');
    if ($handle === FALSE) {
      throw new \InvalidArgumentException(sprintf('Could not open --log file "%s" for writing.', $path));
    }
    $this->logHandle = $handle;
  }

  /**
   * Closes the log file, if one is open.
   */
  private function closeLog(): void {
    if ($this->logHandle !== NULL) {
      fclose($this->logHandle);
      $this->logHandle = NULL;
    }
  }

  /**
   * Picks the path of the directory that holds the ssh control sockets.
   *
   * A directory, not a bare predictable socket path: on a shared host a
   * guessable path in the temp directory could be pre-created by another
   * user. The socket basenames inside are short hashes, because the
   * AF_UNIX path limit is around 104 bytes. The directory is created by
   * createControlDirectory() just before the first connection.
   */
  private function controlPathDirectory(): string {
    $this->controlDirectory = sprintf('%s/ecms-ssh-%s', sys_get_temp_dir(), bin2hex(random_bytes(4)));
    return $this->controlDirectory;
  }

  /**
   * Creates the 0700 control socket directory.
   *
   * mkdir() fails if the path already exists, so a directory pre-created
   * by another user is never reused. On failure the path is forgotten, so
   * cleanUpControlDirectory() does not touch a directory it did not create.
   */
  private function createControlDirectory(): void {
    $directory = (string) $this->controlDirectory;
    if (!@mkdir($directory, 0700)) {
      $this->controlDirectory = NULL;
      throw new \RuntimeException(sprintf('Could not create ssh control directory "%s".', $directory));
    }
  }

  /**
   * Removes the control socket directory so nothing outlives the process.
   */
  private function cleanUpControlDirectory(): void {
    if ($this->controlDirectory === NULL) {
      return;
    }

    foreach (glob($this->controlDirectory . '/*') ?: [] as $socket) {
      @unlink($socket);
    }
    @rmdir($this->controlDirectory);
    $this->controlDirectory = NULL;
  }

  /**
   * Renders a duration in seconds as a rough human-readable string.
   */
  private function humanDuration(int $seconds): string {
    if ($seconds < 3600) {
      return sprintf('%d min', (int) ceil($seconds / 60));
    }
    return sprintf('%.1f hours', $seconds / 3600);
  }

}

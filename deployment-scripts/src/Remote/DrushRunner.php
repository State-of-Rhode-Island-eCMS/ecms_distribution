<?php

declare(strict_types=1);

namespace Ecms\Deployment\Remote;

use Ecms\Deployment\Api\Site;

/**
 * Runs sequences of drush commands on ACSF sites over SSH.
 *
 * Connection details come entirely from the drush site alias, so changing
 * where an environment lives is a YAML edit, never a code change.
 *
 * Connections are multiplexed: the master for a given user@host is opened
 * once, explicitly, and every step afterwards reuses it as a plain client.
 */
final class DrushRunner {

  /**
   * SSH options shared by the master and every client invocation.
   *
   * - BatchMode: never prompt for a password or passphrase. Without it an
   *   unconfigured operator gets a hung process instead of an error.
   * - ConnectTimeout: fail a dead host quickly rather than at --timeout.
   * - ServerAlive*: a sapi-i can run silently for many minutes; without
   *   keepalives a NAT or firewall idle-timeout kills the connection
   *   mid-index and it looks like a drush failure.
   *
   * StrictHostKeyChecking is deliberately absent: it stays at its default.
   * With BatchMode on, an unknown host key fails immediately and cleanly,
   * and preflight turns that into one actionable error rather than N.
   */
  private const COMMON_SSH_OPTIONS = [
    'BatchMode=yes',
    'ConnectTimeout=15',
    'ServerAliveInterval=30',
    'ServerAliveCountMax=6',
  ];

  /**
   * How long an idle multiplexed master lingers, in seconds.
   */
  private const CONTROL_PERSIST_SECONDS = 300;

  /**
   * Control sockets opened by this runner, keyed by user@host.
   *
   * @var array<string, string>
   */
  private array $masters = [];

  /**
   * Constructs a DrushRunner.
   *
   * @param \Ecms\Deployment\Remote\ProcessRunnerInterface $runner
   *   The process runner; injected so tests never touch the network.
   * @param string $controlPathDirectory
   *   A 0700 directory this runner owns. One socket per user@host is
   *   created inside it, so sites on different stacks each get their own
   *   multiplexed master.
   * @param string[] $extraSshOptions
   *   Additional -o values from --ssh-option.
   */
  public function __construct(
    private readonly ProcessRunnerInterface $runner,
    private readonly string $controlPathDirectory,
    private readonly array $extraSshOptions = [],
  ) {}

  /**
   * The control socket path for an alias.
   *
   * The basename is a short hash, never user@host itself: the AF_UNIX
   * sun_path limit is around 104 bytes, and a real Acquia user@host is
   * ~73 characters on its own — under a macOS TMPDIR the unhashed path
   * exceeds the limit and ssh fails obscurely.
   */
  public function controlPathFor(SiteAlias $alias): string {
    return sprintf(
      '%s/%s',
      rtrim($this->controlPathDirectory, '/'),
      substr(hash('sha256', $alias->userAtHost()), 0, 16)
    );
  }

  /**
   * The argv that opens a multiplexed master, without running a command.
   *
   * Opened explicitly rather than letting the first step become the master
   * via ControlMaster=auto. That first ssh would both run the command and
   * become the backgrounded master, inheriting the stdout/stderr pipes — so
   * after the command exits the pipes stay open and Process waits for EOF
   * until the timeout fires. -f -N detaches cleanly with no pipes to hold.
   *
   * @param \Ecms\Deployment\Remote\SiteAlias $alias
   *   The alias whose host to connect to.
   *
   * @return string[]
   *   The argv.
   */
  public function masterCommandFor(SiteAlias $alias): array {
    return [
      'ssh',
      '-f',
      '-N',
      '-M',
      '-n',
      ...$this->optionArguments([
        sprintf('ControlPersist=%d', self::CONTROL_PERSIST_SECONDS),
        sprintf('ControlPath=%s', $this->controlPathFor($alias)),
      ]),
      $alias->userAtHost(),
    ];
  }

  /**
   * The exact argv for one step.
   *
   * Public so tests can assert it without any network access.
   *
   * @param \Ecms\Deployment\Remote\SiteAlias $alias
   *   The alias resolved for this site.
   * @param string $uri
   *   The resolved --uri for this site.
   * @param \Ecms\Deployment\Remote\DrushCommandLine $command
   *   The command to run.
   *
   * @return string[]
   *   The argv. The final element is the remote command string, already
   *   escaped for the remote shell, passed as one opaque argument.
   */
  public function sshCommandFor(SiteAlias $alias, string $uri, DrushCommandLine $command): array {
    return $this->sshCommandForArgv($alias, [
      $alias->drushScript,
      sprintf('--root=%s', $alias->docroot),
      sprintf('--uri=%s', $uri),
      ...$command->argv,
    ]);
  }

  /**
   * The argv for an arbitrary remote command, as a multiplexing client.
   *
   * Takes a raw argv rather than a DrushCommandLine because the preflight
   * probe runs "drush --version", and DrushCommandLine deliberately refuses
   * anything starting with an option — that guard protects operator input
   * and must not be softened to accommodate this internal call.
   *
   * @param \Ecms\Deployment\Remote\SiteAlias $alias
   *   The alias whose host and control socket to use.
   * @param string[] $argv
   *   The remote argv, executable first.
   *
   * @return string[]
   *   The local ssh argv.
   */
  private function sshCommandForArgv(SiteAlias $alias, array $argv): array {
    return [
      'ssh',
      // Never send stdin, and never become a master: this is a client.
      '-n',
      ...$this->optionArguments([
        'ControlMaster=no',
        sprintf('ControlPath=%s', $this->controlPathFor($alias)),
      ]),
      $alias->userAtHost(),
      ShellWord::join($argv),
    ];
  }

  /**
   * Opens the master for an alias and proves the remote drush works.
   *
   * One round trip that validates the SSH key, the host key and the
   * alias's paths.drush-script together, so a misconfiguration surfaces
   * once rather than N identical times.
   */
  public function preflight(SiteAlias $alias, int $timeoutSeconds): ProcessOutcome {
    $key = $alias->userAtHost();
    if (!isset($this->masters[$key])) {
      $this->runner->run($this->masterCommandFor($alias), $timeoutSeconds);
      $this->masters[$key] = $this->controlPathFor($alias);
    }

    // No --root/--uri: this only proves the binary exists and runs, which
    // is what distinguishes a bad paths.drush-script from a bad site.
    return $this->runner->run(
      $this->sshCommandForArgv($alias, [$alias->drushScript, '--version']),
      $timeoutSeconds
    );
  }

  /**
   * Closes every master this runner opened.
   *
   * Best-effort: a master that already exited is not an error.
   */
  public function disconnectAll(int $timeoutSeconds = 15): void {
    foreach ($this->masters as $userAtHost => $controlPath) {
      $this->runner->run([
        'ssh',
        '-n',
        '-o',
        sprintf('ControlPath=%s', $controlPath),
        '-O',
        'exit',
        $userAtHost,
      ], $timeoutSeconds);
    }

    $this->masters = [];
  }

  /**
   * Runs every command on one site, stopping that site at the first failure.
   *
   * @param \Ecms\Deployment\Api\Site $site
   *   The site being operated on.
   * @param \Ecms\Deployment\Remote\SiteAlias $alias
   *   The alias resolved for this site; a per-site group may point at a
   *   different stack, which is why this is a parameter and not ctor state.
   * @param string $uri
   *   The resolved --uri.
   * @param \Ecms\Deployment\Remote\DrushCommandLine[] $commands
   *   The commands, in order.
   * @param int $timeoutSeconds
   *   Per-step timeout.
   * @param bool $continueOnError
   *   Run the remaining steps even after one fails.
   * @param callable|null $onOutput
   *   Called as fn (string $type, string $chunk) as output arrives.
   * @param callable|null $onStep
   *   Called as fn (int $index, StepResult $result) after each step.
   */
  public function runSequence(
    Site $site,
    SiteAlias $alias,
    string $uri,
    array $commands,
    int $timeoutSeconds,
    bool $continueOnError = FALSE,
    ?callable $onOutput = NULL,
    ?callable $onStep = NULL,
  ): SiteRunResult {
    $steps = [];
    $aborted = FALSE;

    foreach (array_values($commands) as $index => $command) {
      if ($aborted) {
        $steps[] = $result = StepResult::skipped($command);
        if ($onStep !== NULL) {
          $onStep($index, $result);
        }
        continue;
      }

      $startedAt = microtime(TRUE);
      $outcome = $this->runner->run(
        $this->sshCommandFor($alias, $uri, $command),
        $timeoutSeconds,
        $onOutput
      );
      $duration = microtime(TRUE) - $startedAt;

      $status = match (TRUE) {
        $outcome->timedOut => StepStatus::TimedOut,
        $outcome->exitCode === 0 => StepStatus::Ok,
        default => StepStatus::Failed,
      };

      $steps[] = $result = new StepResult(
        $command,
        $status,
        $outcome->exitCode,
        $duration,
        $status->isOk() ? NULL : self::failureText($outcome)
      );
      if ($onStep !== NULL) {
        $onStep($index, $result);
      }

      if (!$status->isOk() && !$continueOnError) {
        $aborted = TRUE;
      }
    }

    return new SiteRunResult($site, $uri, $steps);
  }

  /**
   * The most useful text to show for a failed step.
   *
   * Prefers stderr, falls back to stdout — drush does not reliably use
   * stderr — and reports a timeout explicitly, since a timed-out process
   * may have produced no output at all.
   */
  private static function failureText(ProcessOutcome $outcome): ?string {
    if ($outcome->timedOut) {
      return 'Timed out.';
    }

    $stderr = trim($outcome->errorOutput);
    if ($stderr !== '') {
      return $stderr;
    }

    $stdout = trim($outcome->output);
    return $stdout !== '' ? $stdout : NULL;
  }

  /**
   * Expands option values into alternating -o arguments.
   *
   * @param string[] $options
   *   Option values, without the -o.
   *
   * @return string[]
   *   Flattened -o pairs, with --ssh-option passthrough last so an
   *   operator can override a default.
   */
  private function optionArguments(array $options): array {
    $arguments = [];
    foreach ([...self::COMMON_SSH_OPTIONS, ...$options, ...$this->extraSshOptions] as $option) {
      $arguments[] = '-o';
      $arguments[] = $option;
    }
    return $arguments;
  }

}

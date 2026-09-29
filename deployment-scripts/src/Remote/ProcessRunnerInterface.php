<?php

declare(strict_types=1);

namespace Ecms\Deployment\Remote;

/**
 * Runs an external process.
 *
 * This is the seam that makes an SSH shell-out testable offline, mirroring
 * the $handler constructor seam SiteHealthChecker uses for Guzzle. It is
 * also where concurrency would be added later, without DrushRunCommand
 * having to change.
 */
interface ProcessRunnerInterface {

  /**
   * Runs a command and waits for it to finish.
   *
   * @param string[] $command
   *   The full argv. Implementations must never pass this through a local
   *   shell: the remote command string is one opaque argv entry, already
   *   escaped for the remote shell.
   * @param int $timeoutSeconds
   *   How long to allow before killing the process.
   * @param callable|null $onOutput
   *   Called as fn (string $type, string $chunk): void as output arrives,
   *   where $type is 'out' or 'err'.
   */
  public function run(array $command, int $timeoutSeconds, ?callable $onOutput = NULL): ProcessOutcome;

}

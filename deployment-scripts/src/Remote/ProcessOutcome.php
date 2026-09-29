<?php

declare(strict_types=1);

namespace Ecms\Deployment\Remote;

/**
 * The result of running one external process.
 *
 * Both streams are retained. Drush does not reliably put diagnostics on
 * stderr — a step can fail with everything useful on stdout — so keeping
 * only errorOutput would leave the results table's Error column blank for
 * exactly those failures.
 */
final class ProcessOutcome {

  /**
   * Constructs a ProcessOutcome.
   *
   * @param int|null $exitCode
   *   The process exit code, or NULL if it timed out or never started.
   * @param bool $timedOut
   *   Whether the process exceeded its timeout.
   * @param string $errorOutput
   *   A bounded tail of stderr.
   * @param string $output
   *   A bounded tail of stdout.
   */
  public function __construct(
    public readonly ?int $exitCode,
    public readonly bool $timedOut,
    public readonly string $errorOutput = '',
    public readonly string $output = '',
  ) {}

  /**
   * Whether the process succeeded.
   */
  public function succeeded(): bool {
    return !$this->timedOut && $this->exitCode === 0;
  }

}

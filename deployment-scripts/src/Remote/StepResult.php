<?php

declare(strict_types=1);

namespace Ecms\Deployment\Remote;

/**
 * The outcome of one drush command on one site.
 */
final class StepResult {

  /**
   * Constructs a StepResult.
   *
   * @param \Ecms\Deployment\Remote\DrushCommandLine $command
   *   The command this step ran.
   * @param \Ecms\Deployment\Remote\StepStatus $status
   *   How it ended.
   * @param int|null $exitCode
   *   The drush exit code, or NULL if it timed out or never ran.
   * @param float $durationSeconds
   *   Wall-clock duration; 0.0 for a step that never ran.
   * @param string|null $error
   *   Operator-facing failure text, or NULL on success.
   */
  public function __construct(
    public readonly DrushCommandLine $command,
    public readonly StepStatus $status,
    public readonly ?int $exitCode,
    public readonly float $durationSeconds,
    public readonly ?string $error,
  ) {}

  /**
   * Builds the result for a step that succeeded.
   */
  public static function ok(DrushCommandLine $command, float $durationSeconds): self {
    return new self($command, StepStatus::Ok, 0, $durationSeconds, NULL);
  }

  /**
   * Builds the result for a step that never ran.
   *
   * Used both for the skip cascade after a failure and for a site that
   * could not be resolved at all.
   */
  public static function skipped(DrushCommandLine $command): self {
    return new self($command, StepStatus::Skipped, NULL, 0.0, NULL);
  }

  /**
   * Builds the result for a step that failed before any process started.
   */
  public static function failedBeforeRunning(DrushCommandLine $command, string $reason): self {
    return new self($command, StepStatus::Failed, NULL, 0.0, $reason);
  }

}

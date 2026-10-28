<?php

declare(strict_types=1);

namespace Ecms\Deployment\Remote;

/**
 * The outcome of one drush command on one site.
 */
enum StepStatus: string {

  case Ok = 'OK';
  case Failed = 'FAILED';
  case TimedOut = 'TIMED OUT';
  case Skipped = 'SKIPPED';

  /**
   * Whether this status counts as success.
   */
  public function isOk(): bool {
    return $this === self::Ok;
  }

  /**
   * The status wrapped in console colour tags for the results table.
   */
  public function formatted(): string {
    return match ($this) {
      self::Ok => '<fg=green>OK</>',
      self::Failed, self::TimedOut => sprintf('<fg=red>%s</>', $this->value),
      self::Skipped => '<fg=yellow>SKIPPED</>',
    };
  }

}

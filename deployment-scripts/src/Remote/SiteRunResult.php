<?php

declare(strict_types=1);

namespace Ecms\Deployment\Remote;

use Ecms\Deployment\Api\Site;

/**
 * Every step's outcome for one site.
 */
final class SiteRunResult {

  /**
   * Constructs a SiteRunResult.
   *
   * @param \Ecms\Deployment\Api\Site $site
   *   The site this run targeted.
   * @param string|null $uri
   *   The resolved --uri, or NULL if it could not be resolved.
   * @param \Ecms\Deployment\Remote\StepResult[] $steps
   *   One result per command, in order.
   */
  public function __construct(
    public readonly Site $site,
    public readonly ?string $uri,
    public readonly array $steps,
  ) {}

  /**
   * Builds the result for a site that never got as far as SSH.
   *
   * The site must not silently vanish from the results table or skew the
   * audit totals, so this produces the same shape every other site has:
   * step 1 failed with the reason, every later command skipped.
   *
   * @param \Ecms\Deployment\Api\Site $site
   *   The site that could not be resolved.
   * @param string $reason
   *   Operator-facing explanation, shown in the Error column.
   * @param \Ecms\Deployment\Remote\DrushCommandLine[] $commands
   *   The commands that would have run.
   */
  public static function unresolved(Site $site, string $reason, array $commands): self {
    $steps = [];
    foreach (array_values($commands) as $index => $command) {
      $steps[] = $index === 0
        ? StepResult::failedBeforeRunning($command, $reason)
        : StepResult::skipped($command);
    }

    return new self($site, NULL, $steps);
  }

  /**
   * Whether this site needs the operator's attention.
   *
   * An empty step list counts as failed: it means the runner did no work,
   * which is never a success. Reporting "no failing step found" as green
   * would turn a silent no-op into a passing run.
   */
  public function failed(): bool {
    if ($this->steps === []) {
      return TRUE;
    }

    foreach ($this->steps as $step) {
      if (!$step->status->isOk()) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Counts the steps ending in a given status.
   */
  public function countByStatus(StepStatus $status): int {
    return count(array_filter($this->steps, static fn (StepResult $s): bool => $s->status === $status));
  }

}

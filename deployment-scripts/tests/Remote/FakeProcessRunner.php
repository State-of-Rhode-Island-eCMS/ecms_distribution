<?php

declare(strict_types=1);

namespace Ecms\Deployment\Tests\Remote;

use Ecms\Deployment\Remote\ProcessOutcome;
use Ecms\Deployment\Remote\ProcessRunnerInterface;

/**
 * Records every argv and returns scripted outcomes, so the SSH layer is
 * exercised with no network access at all.
 *
 * Invocations are tagged so tests can count *steps* separately from
 * connection management (master open, preflight probe, ssh -O exit). A
 * bare total would drift silently if the connection lifecycle changed.
 */
final class FakeProcessRunner implements ProcessRunnerInterface {

  /**
   * Every recorded invocation: ['argv' => string[], 'kind' => string].
   *
   * @var array<int, array{argv: string[], kind: string, timeout: int}>
   */
  public array $invocations = [];

  /**
   * Outcomes returned in order for 'step' invocations.
   *
   * @var \Ecms\Deployment\Remote\ProcessOutcome[]
   */
  private array $stepOutcomes;

  /**
   * @param \Ecms\Deployment\Remote\ProcessOutcome[] $stepOutcomes
   *   Returned in order for step invocations; exhausting the list yields a
   *   success, so the happy path needs no setup.
   * @param string $streamChunk
   *   Text handed to the $onOutput callback for each step, to exercise
   *   streaming without a real process.
   * @param \Ecms\Deployment\Remote\ProcessOutcome|null $probeOutcome
   *   Returned for the preflight "drush --version" probe. Defaults to
   *   success; set a failure to exercise the abort-before-any-step path.
   */
  public function __construct(
    array $stepOutcomes = [],
    private readonly string $streamChunk = '',
    private readonly ?ProcessOutcome $probeOutcome = NULL,
  ) {
    $this->stepOutcomes = $stepOutcomes;
  }

  /**
   * {@inheritdoc}
   */
  public function run(array $command, int $timeoutSeconds, ?callable $onOutput = NULL): ProcessOutcome {
    $kind = $this->classify($command);
    $this->invocations[] = ['argv' => $command, 'kind' => $kind, 'timeout' => $timeoutSeconds];

    if ($kind === 'probe' && $this->probeOutcome !== NULL) {
      return $this->probeOutcome;
    }

    if ($kind !== 'step') {
      return new ProcessOutcome(0, FALSE);
    }

    if ($onOutput !== NULL && $this->streamChunk !== '') {
      $onOutput('out', $this->streamChunk);
    }

    return array_shift($this->stepOutcomes) ?? new ProcessOutcome(0, FALSE);
  }

  /**
   * Argvs of invocations of one kind.
   *
   * @return array<int, string[]>
   */
  public function argvsOfKind(string $kind): array {
    return array_values(array_map(
      static fn (array $i): array => $i['argv'],
      array_filter($this->invocations, static fn (array $i): bool => $i['kind'] === $kind)
    ));
  }

  /**
   * How many invocations of one kind were recorded.
   */
  public function countOfKind(string $kind): int {
    return count($this->argvsOfKind($kind));
  }

  /**
   * Classifies an invocation by its ssh arguments.
   */
  private function classify(array $command): string {
    if (in_array('-M', $command, TRUE)) {
      return 'master';
    }
    if (in_array('-O', $command, TRUE)) {
      return 'disconnect';
    }
    if (str_ends_with((string) end($command), "'--version'")) {
      return 'probe';
    }
    return 'step';
  }

}

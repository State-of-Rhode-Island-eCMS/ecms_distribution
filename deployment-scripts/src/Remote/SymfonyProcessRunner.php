<?php

declare(strict_types=1);

namespace Ecms\Deployment\Remote;

use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

/**
 * The real process runner.
 *
 * This is the only class in the tool that touches Symfony's Process; every
 * other class is exercised offline through ProcessRunnerInterface.
 */
final class SymfonyProcessRunner implements ProcessRunnerInterface {

  /**
   * How much of each stream to retain for error reporting, in bytes.
   *
   * A sapi-i run can emit a great deal of output over half an hour. The
   * live stream and --log already carry the full text; only a tail is kept
   * in memory for the results table.
   */
  private const OUTPUT_TAIL_BYTES = 4096;

  /**
   * {@inheritdoc}
   */
  public function run(array $command, int $timeoutSeconds, ?callable $onOutput = NULL): ProcessOutcome {
    // The array form never invokes a local shell, so the remote command
    // string is passed through as a single opaque argument and is
    // interpreted only by the remote login shell.
    $process = new Process($command);
    $process->setTimeout($timeoutSeconds);

    $stdout = '';
    $stderr = '';
    $collect = function (string $type, string $chunk) use (&$stdout, &$stderr, $onOutput): void {
      if ($type === Process::ERR) {
        $stderr = self::appendTail($stderr, $chunk);
      }
      else {
        $stdout = self::appendTail($stdout, $chunk);
      }
      if ($onOutput !== NULL) {
        $onOutput($type === Process::ERR ? 'err' : 'out', $chunk);
      }
    };

    try {
      $process->run($collect);
    }
    catch (ProcessTimedOutException) {
      return new ProcessOutcome(NULL, TRUE, $stderr, $stdout);
    }

    return new ProcessOutcome($process->getExitCode(), FALSE, $stderr, $stdout);
  }

  /**
   * Appends a chunk, keeping only the trailing OUTPUT_TAIL_BYTES.
   */
  private static function appendTail(string $buffer, string $chunk): string {
    $buffer .= $chunk;
    if (strlen($buffer) > self::OUTPUT_TAIL_BYTES) {
      $buffer = substr($buffer, -self::OUTPUT_TAIL_BYTES);
    }
    return $buffer;
  }

}

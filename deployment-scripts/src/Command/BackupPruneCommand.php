<?php

declare(strict_types=1);

namespace Ecms\Deployment\Command;

use Ecms\Deployment\Api\BackupRepository;
use Ecms\Deployment\Api\Exception\AcsfApiException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Deletes backups older than a retention window for one or more sites.
 */
#[AsCommand(
  name: 'ecms:backup:prune',
  description: 'Deletes backups older than a retention window for one or more sites.',
)]
final class BackupPruneCommand extends AbstractAcsfCommand {

  private const PRODUCTION_CONFIRMATION_FLAG = 'i-know-this-is-production';

  /**
   * {@inheritdoc}
   */
  protected function configure(): void {
    parent::configure();
    $this
      ->addOption(
        'retention-days',
        NULL,
        InputOption::VALUE_REQUIRED,
        'Backups older than this many days are deleted.',
        '30'
      )
      ->addOption(
        'dry-run',
        NULL,
        InputOption::VALUE_NONE,
        'List backups that would be deleted, without deleting anything.'
      )
      ->addOption(
        'force',
        NULL,
        InputOption::VALUE_NONE,
        'Skip the interactive confirmation prompt. Required for non-interactive (e.g. CI) runs.'
      )
      ->addOption(
        self::PRODUCTION_CONFIRMATION_FLAG,
        NULL,
        InputOption::VALUE_NONE,
        'Required in addition to --force when targeting production (--prod).'
      );
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $retentionDays = filter_var($input->getOption('retention-days'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    if ($retentionDays === FALSE) {
      throw new \InvalidArgumentException(sprintf(
        '--retention-days must be a non-negative integer, got "%s".',
        $input->getOption('retention-days')
      ));
    }

    $dryRun = (bool) $input->getOption('dry-run');

    if (!$dryRun) {
      $this->requireExplicitProductionConfirmation($input, self::PRODUCTION_CONFIRMATION_FLAG);

      if (!$input->isInteractive() && !$input->getOption('force')) {
        throw new \InvalidArgumentException('Refusing to delete backups in a non-interactive run without --force.');
      }
      if ($input->isInteractive() && !$input->getOption('force')) {
        $confirmed = $this->io->confirm(sprintf(
          'This will permanently delete backups older than %d day(s) in %s (%s). Continue?',
          $retentionDays,
          $this->environment->value,
          $this->environment->acquiaEnvironment()
        ), FALSE);
        if (!$confirmed) {
          $this->io->warning('Aborted; no backups were deleted.');
          return Command::SUCCESS;
        }
      }
    }

    $sites = $this->resolveSites($input);
    $backupRepository = new BackupRepository($this->client);
    $cutoffTimestamp = strtotime(sprintf('-%d days', $retentionDays));

    $rows = [];
    $failures = 0;

    foreach ($sites as $site) {
      try {
        foreach ($backupRepository->forSite($site->id) as $backup) {
          if ($backup->timestamp >= $cutoffTimestamp) {
            continue;
          }

          if ($dryRun) {
            $rows[] = [$site->id, $site->domain, $backup->id, $backup->label, 'would delete'];
            continue;
          }

          $taskId = $backupRepository->delete($site->id, $backup->id);
          $rows[] = [$site->id, $site->domain, $backup->id, $backup->label, sprintf('deleted (task %d)', $taskId)];
        }
      }
      catch (AcsfApiException $e) {
        // A site with no backups at all returns 404; that is not a failure.
        if ($e->statusCode === 404) {
          continue;
        }
        $failures++;
        $rows[] = [$site->id, $site->domain, '—', '—', 'FAILED: ' . $e->getMessage()];
      }
    }

    $this->io->table(['Site ID', 'Domain', 'Backup ID', 'Label', 'Result'], $rows);

    if ($rows === []) {
      $this->io->success('No backups matched the retention window.');
    }
    elseif ($dryRun) {
      $this->io->note(sprintf('%d backup(s) would be deleted (dry run).', count($rows)));
    }

    if ($failures > 0) {
      $this->io->error(sprintf('%d site(s) failed while pruning backups.', $failures));
      return Command::FAILURE;
    }

    return Command::SUCCESS;
  }

}

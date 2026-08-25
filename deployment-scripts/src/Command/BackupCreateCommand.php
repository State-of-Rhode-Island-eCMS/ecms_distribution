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
 * Requests a new backup for one or more sites in an ACSF factory.
 */
#[AsCommand(
  name: 'ecms:backup:create',
  description: 'Requests a new backup for one or more sites in an ACSF factory.',
)]
final class BackupCreateCommand extends AbstractAcsfCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure(): void {
    parent::configure();
    $this->addOption(
      'components',
      NULL,
      InputOption::VALUE_REQUIRED,
      "Comma-separated backup components (e.g. database,public files,private files,themes). 'codebase' is deliberately excluded from the default since it is identical across every site and cannot be restored into the factory.",
      'database'
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $sites = $this->resolveSites($input);
    $components = array_map('trim', explode(',', (string) $input->getOption('components')));
    $backupRepository = new BackupRepository($this->client);

    $rows = [];
    $failures = 0;

    foreach ($sites as $site) {
      try {
        $taskId = $backupRepository->create($site->id, $components);
        $rows[] = [$site->id, $site->domain, 'requested', $taskId];
      }
      catch (AcsfApiException $e) {
        $failures++;
        $rows[] = [$site->id, $site->domain, 'FAILED', $e->getMessage()];
      }
    }

    $this->io->table(['Site ID', 'Domain', 'Status', 'Task ID / Error'], $rows);

    if ($failures > 0) {
      $this->io->error(sprintf('%d of %d backup request(s) failed.', $failures, count($sites)));
      return Command::FAILURE;
    }

    $this->io->success(sprintf('Requested backups for %d site(s).', count($sites)));
    return Command::SUCCESS;
  }

}

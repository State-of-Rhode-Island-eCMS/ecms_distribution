<?php

declare(strict_types=1);

namespace Ecms\Deployment\Command;

use Ecms\Deployment\Health\SiteHealthChecker;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Checks that sites in a factory are responding.
 *
 * Useful as a post-upgrade smoke test.
 */
#[AsCommand(
  name: 'ecms:site:status',
  description: 'Checks that sites in a factory are responding; useful as a post-upgrade smoke test.',
)]
final class SiteStatusCommand extends AbstractAcsfCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure(): void {
    parent::configure();
    $this
      ->addOption('path', NULL, InputOption::VALUE_REQUIRED, 'The path to request on each site.', '/user')
      ->addOption('concurrency', NULL, InputOption::VALUE_REQUIRED, 'Maximum number of sites to check at once.', '10')
      ->addOption('format', NULL, InputOption::VALUE_REQUIRED, 'Output format: table or json.', 'table')
      ->addOption(
        'insecure',
        NULL,
        InputOption::VALUE_NONE,
        'Skip TLS certificate verification for this check. Only affects this command; the authenticated ACSF API client always verifies TLS. Use only to troubleshoot a site with a known, temporary certificate problem.'
      );
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $sites = $this->resolveSites($input);
    $path = (string) $input->getOption('path');
    $concurrency = max(1, (int) $input->getOption('concurrency'));
    $format = (string) $input->getOption('format');
    $insecure = (bool) $input->getOption('insecure');

    if ($insecure) {
      $this->io->warning('--insecure: skipping TLS certificate verification for this check.');
    }

    // Deliberately built without factory API credentials: a site's own
    // domain must never see the ACSF Basic auth used against the API.
    $checker = new SiteHealthChecker(
      concurrency: $concurrency,
      caBundle: $input->getOption('ca-bundle'),
      skipTlsVerification: $insecure,
    );
    $results = $checker->checkAll($sites, $path);

    $unhealthy = array_filter($results, static fn ($r) => !$r->healthy);

    if ($format === 'json') {
      $output->writeln(json_encode(array_map(static fn ($r) => [
        'site_id' => $r->site->id,
        'domain' => $r->site->domain,
        'url' => $r->url,
        'healthy' => $r->healthy,
        'status_code' => $r->statusCode,
        'error' => $r->error,
      ], $results), JSON_PRETTY_PRINT));
    }
    else {
      $rows = array_map(static fn ($r) => [
        $r->site->id,
        $r->url,
        $r->healthy ? '<fg=green>UP</>' : '<fg=red>DOWN</>',
        $r->statusCode ?? '—',
        $r->error ?? '',
      ], $results);
      $this->io->table(['Site ID', 'URL', 'Status', 'HTTP', 'Error'], $rows);
    }

    if ($unhealthy !== []) {
      $this->io->error(sprintf('%d of %d site(s) are unhealthy.', count($unhealthy), count($results)));
      return Command::FAILURE;
    }

    $this->io->success(sprintf('All %d site(s) are healthy.', count($results)));
    return Command::SUCCESS;
  }

}

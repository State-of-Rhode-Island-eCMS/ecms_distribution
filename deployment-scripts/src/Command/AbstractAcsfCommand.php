<?php

declare(strict_types=1);

namespace Ecms\Deployment\Command;

use Ecms\Deployment\Api\AcsfClient;
use Ecms\Deployment\Api\Site;
use Ecms\Deployment\Api\SiteRepository;
use Ecms\Deployment\Config\FactoryConfig;
use Ecms\Deployment\Config\FactoryEnvironment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Base for every ecms:* command.
 *
 * Resolves --env/--prod, builds the authenticated API client, and resolves
 * the shared --sites option.
 */
abstract class AbstractAcsfCommand extends Command {

  /**
   * The console output helper, built once per run in initialize().
   */
  protected SymfonyStyle $io;

  /**
   * The factory environment this run targets, resolved from --env/--prod.
   */
  protected FactoryEnvironment $environment;

  /**
   * The resolved, validated configuration for $environment.
   */
  protected FactoryConfig $config;

  /**
   * The authenticated ACSF API client for $environment.
   */
  protected AcsfClient $client;

  /**
   * A SiteRepository built from $client.
   */
  protected SiteRepository $siteRepository;

  /**
   * Constructs an ecms:* command.
   *
   * @param \Ecms\Deployment\Api\AcsfClient|null $injectedClient
   *   Test-only seam: when given, initialize() uses this client instead of
   *   building one from FactoryConfig, so tests can inject an AcsfClient
   *   backed by a Guzzle MockHandler without any real credentials or
   *   network access. Production code (bin/ecms) never passes this.
   */
  public function __construct(private readonly ?AcsfClient $injectedClient = NULL) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure(): void {
    $this
      ->addOption(
        'env',
        NULL,
        InputOption::VALUE_REQUIRED,
        sprintf(
          'The factory environment to target (%s). Defaults to "%s" — production is only used when explicitly requested.',
          implode('|', array_column(FactoryEnvironment::cases(), 'value')),
          FactoryEnvironment::DEFAULT->value
        )
      )
      ->addOption(
        'prod',
        NULL,
        InputOption::VALUE_NONE,
        'Shorthand for --env=prod.'
      )
      ->addOption(
        'sites',
        NULL,
        InputOption::VALUE_REQUIRED,
        'Comma-separated list of site IDs, or "all" (default) for every site in the target environment.',
        'all'
      )
      ->addOption(
        'ca-bundle',
        NULL,
        InputOption::VALUE_REQUIRED,
        'Path to an alternate CA bundle. TLS verification is always on; use this only if a specific factory requires a non-standard CA, never to disable verification.'
      );
  }

  /**
   * {@inheritdoc}
   */
  protected function initialize(InputInterface $input, OutputInterface $output): void {
    $this->io = new SymfonyStyle($input, $output);
    $this->environment = FactoryEnvironment::fromInput($input);
    $this->config = FactoryConfig::fromEnvironment($this->environment);
    $this->client = $this->injectedClient ?? new AcsfClient($this->config, $input->getOption('ca-bundle'));
    $this->siteRepository = new SiteRepository($this->client);

    $banner = sprintf(
      'Target: %s (%s) — %s',
      $this->environment->value,
      $this->environment->acquiaEnvironment(),
      $this->config->apiBaseUri
    );
    if ($this->environment->isProduction()) {
      $this->io->warning($banner);
    }
    else {
      $this->io->note($banner);
    }
  }

  /**
   * Resolves the --sites option into concrete Site objects.
   *
   * "all" (the default) streams every site in the target environment.
   * An explicit comma-separated list is validated up front — any
   * non-numeric or non-positive entry is a hard error naming the offender,
   * never silently skipped — and each ID is then looked up individually,
   * which doubles as confirming the site actually exists before any
   * destructive command acts on it.
   *
   * @return \Ecms\Deployment\Api\Site[]
   *   The resolved sites.
   */
  protected function resolveSites(InputInterface $input): array {
    $sitesOption = (string) $input->getOption('sites');

    if ($sitesOption === '' || strtolower($sitesOption) === 'all') {
      return iterator_to_array($this->siteRepository->all(), FALSE);
    }

    $ids = $this->parseSiteIds($sitesOption);

    return array_map(
      fn (int $id): Site => $this->siteRepository->find($id),
      $ids
    );
  }

  /**
   * Parses and validates a comma-separated list of site IDs.
   *
   * @return int[]
   *   The validated, deduplicated site IDs.
   */
  protected function parseSiteIds(string $value): array {
    $rawIds = array_filter(array_map('trim', explode(',', $value)), fn (string $v): bool => $v !== '');

    $invalid = [];
    $valid = [];
    foreach ($rawIds as $rawId) {
      $id = filter_var($rawId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
      if ($id === FALSE) {
        $invalid[] = $rawId;
        continue;
      }
      $valid[] = $id;
    }

    if ($invalid !== []) {
      throw new \InvalidArgumentException(sprintf(
        'Invalid site ID(s) in --sites: %s. Site IDs must be positive integers.',
        implode(', ', $invalid)
      ));
    }

    return array_values(array_unique($valid));
  }

  /**
   * Enforces the extra production guardrail for destructive commands.
   *
   * On --prod, --force alone is not enough — the operator must also pass
   * $flag to prove the choice was deliberate.
   */
  protected function requireExplicitProductionConfirmation(InputInterface $input, string $flag): void {
    if ($this->environment->isProduction() && !$input->getOption($flag)) {
      throw new \InvalidArgumentException(sprintf(
        'Refusing to run a destructive command against production without --%s.',
        $flag
      ));
    }
  }

}

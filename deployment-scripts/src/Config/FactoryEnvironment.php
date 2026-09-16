<?php

declare(strict_types=1);

namespace Ecms\Deployment\Config;

use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputInterface;

/**
 * The ACSF factory a command run targets.
 *
 * This is the single source of truth mapping a CLI-facing name to an Acquia
 * Cloud environment and a Site Factory hostname. The names mirror the
 * existing drush site aliases (drush/sites/01dev.site.yml, 01test.site.yml,
 * 01live.site.yml) and the AH_SITE_ENVIRONMENT values used across
 * factory-hooks/, so the vocabulary is consistent everywhere in the repo.
 */
enum FactoryEnvironment: string {
  case Dev = 'dev';
  case Test = 'test';
  case Prod = 'prod';

  /**
   * The environment every command falls back to unless told otherwise.
   *
   * Nothing this tool does should ever touch production by accident, so the
   * default is stage, never prod.
   */
  public const DEFAULT = self::Test;

  /**
   * Resolves the target environment from the `--env` and `--prod` options.
   *
   * `--prod` is a shorthand for `--env=prod`; combining it with a
   * conflicting `--env` value is rejected rather than silently prioritized.
   */
  public static function fromInput(InputInterface $input): self {
    $envOption = $input->getOption('env');
    $prodFlag = (bool) $input->getOption('prod');

    if ($prodFlag && $envOption !== NULL && $envOption !== self::Prod->value) {
      throw new InvalidOptionException(sprintf(
        'The --prod flag conflicts with --env=%s. Pass only one of them.',
        $envOption
      ));
    }

    if ($prodFlag) {
      return self::Prod;
    }

    if ($envOption === NULL) {
      return self::DEFAULT;
    }

    return self::fromString($envOption);
  }

  /**
   * Resolves an environment from its CLI-facing string value.
   */
  public static function fromString(string $value): self {
    $environment = self::tryFrom($value);
    if ($environment === NULL) {
      throw new \InvalidArgumentException(sprintf(
        'Unknown environment "%s". Valid values are: %s.',
        $value,
        implode(', ', array_column(self::cases(), 'value'))
      ));
    }
    return $environment;
  }

  /**
   * The Acquia Cloud environment name, e.g. for banners and log lines.
   */
  public function acquiaEnvironment(): string {
    return match ($this) {
      self::Dev => '01dev',
      self::Test => '01test',
      self::Prod => '01live',
    };
  }

  /**
   * The Site Factory hostname for this environment.
   */
  public function factoryHost(): string {
    return match ($this) {
      self::Dev => 'www.dev-riecms.acsitefactory.com',
      self::Test => 'www.test-riecms.acsitefactory.com',
      self::Prod => 'www.riecms.acsitefactory.com',
    };
  }

  /**
   * The base URI for the ACSF REST API in this environment.
   */
  public function apiBaseUri(): string {
    return sprintf('https://%s/api/v1/', $this->factoryHost());
  }

  /**
   * The environment variable suffix used to look up per-environment secrets.
   *
   * E.g. ACSF_API_KEY_PROD, ACSF_API_KEY_TEST, ACSF_API_KEY_DEV.
   */
  public function envVarSuffix(): string {
    return strtoupper($this->value);
  }

  /**
   * Whether this is the production environment.
   */
  public function isProduction(): bool {
    return $this === self::Prod;
  }

}

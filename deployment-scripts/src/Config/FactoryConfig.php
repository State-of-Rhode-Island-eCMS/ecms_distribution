<?php

declare(strict_types=1);

namespace Ecms\Deployment\Config;

/**
 * Resolved, validated configuration for talking to one ACSF factory.
 *
 * Never log or dump $apiKey directly; use (string) $config->apiKey or
 * var_dump()/print_r() on the config itself, both of which are overridden
 * below to redact it.
 */
final class FactoryConfig {

  private function __construct(
    public readonly FactoryEnvironment $environment,
    public readonly string $apiBaseUri,
    public readonly string $apiUser,
    private readonly RedactedSecret $apiKey,
    public readonly int $requestTimeoutSeconds,
  ) {}

  /**
   * Builds configuration for the given environment from environment variables.
   *
   * Throws with the full list of what's missing if anything required is
   * absent, rather than failing on the first missing variable.
   */
  public static function fromEnvironment(FactoryEnvironment $environment): self {
    $missing = [];

    $apiUser = self::readEnv('ACSF_API_USER');
    if ($apiUser === NULL) {
      $missing[] = 'ACSF_API_USER';
    }

    // Prefer a per-environment key (e.g. ACSF_API_KEY_PROD) so an operator
    // can hold separate stage/prod credentials without swapping exports;
    // fall back to the bare ACSF_API_KEY when no suffixed variable is set.
    $apiKey = self::readEnv('ACSF_API_KEY_' . $environment->envVarSuffix())
      ?? self::readEnv('ACSF_API_KEY');
    if ($apiKey === NULL) {
      $missing[] = sprintf(
        'ACSF_API_KEY_%s (or ACSF_API_KEY)',
        $environment->envVarSuffix()
      );
    }

    if ($missing !== []) {
      throw new \InvalidArgumentException(sprintf(
        'Missing required environment variable(s) for the "%s" environment: %s.',
        $environment->value,
        implode(', ', $missing)
      ));
    }

    $timeout = self::readEnv('ACSF_REQUEST_TIMEOUT');
    $requestTimeoutSeconds = $timeout !== NULL ? (int) $timeout : 30;

    // ACSF_FACTORY_URL is an override only, for a factory whose hostname
    // doesn't follow the dev/test/prod pattern; the default comes from the
    // resolved environment itself.
    $apiBaseUri = self::readEnv('ACSF_FACTORY_URL') ?? $environment->apiBaseUri();

    return new self($environment, $apiBaseUri, $apiUser, new RedactedSecret($apiKey), $requestTimeoutSeconds);
  }

  /**
   * The [user, key] pair Guzzle expects for the 'auth' request option.
   *
   * This is the one place the raw key is revealed.
   */
  public function httpAuth(): array {
    return [$this->apiUser, $this->apiKey->reveal()];
  }

  /**
   * Prevents the API key from ever appearing in a var_dump().
   */
  public function __debugInfo(): array {
    return [
      'environment' => $this->environment->value,
      'apiBaseUri' => $this->apiBaseUri,
      'apiUser' => $this->apiUser,
      'apiKey' => (string) $this->apiKey,
      'requestTimeoutSeconds' => $this->requestTimeoutSeconds,
    ];
  }

  /**
   * Reads an environment variable, treating an empty string as unset.
   */
  private static function readEnv(string $name): ?string {
    $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);
    if ($value === FALSE || $value === NULL || $value === '') {
      return NULL;
    }
    return $value;
  }

}

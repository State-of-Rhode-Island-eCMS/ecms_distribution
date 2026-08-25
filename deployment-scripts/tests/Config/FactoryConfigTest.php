<?php

declare(strict_types=1);

namespace Ecms\Deployment\Tests\Config;

use Ecms\Deployment\Config\FactoryConfig;
use Ecms\Deployment\Config\FactoryEnvironment;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class FactoryConfigTest extends TestCase {

  private const MANAGED_VARS = [
    'ACSF_API_USER',
    'ACSF_API_KEY',
    'ACSF_API_KEY_DEV',
    'ACSF_API_KEY_TEST',
    'ACSF_API_KEY_PROD',
    'ACSF_REQUEST_TIMEOUT',
    'ACSF_FACTORY_URL',
  ];

  protected function tearDown(): void {
    foreach (self::MANAGED_VARS as $var) {
      putenv($var);
      unset($_ENV[$var], $_SERVER[$var]);
    }
    parent::tearDown();
  }

  public function testMissingVarsAreAllListedAtOnce(): void {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Missing required environment variable(s) for the "test" environment: ACSF_API_USER, ACSF_API_KEY_TEST (or ACSF_API_KEY).');
    FactoryConfig::fromEnvironment(FactoryEnvironment::Test);
  }

  public function testApiKeyIsRedactedInDebugInfo(): void {
    putenv('ACSF_API_USER=pfrilling');
    putenv('ACSF_API_KEY=super-secret-value');

    $config = FactoryConfig::fromEnvironment(FactoryEnvironment::Test);

    $dumped = print_r($config, TRUE);
    $this->assertStringNotContainsString('super-secret-value', $dumped);
    $this->assertStringContainsString('***redacted***', $dumped);
  }

  public function testApiKeyIsRevealedOnlyForHttpAuth(): void {
    putenv('ACSF_API_USER=pfrilling');
    putenv('ACSF_API_KEY=super-secret-value');

    $config = FactoryConfig::fromEnvironment(FactoryEnvironment::Test);

    $this->assertSame(['pfrilling', 'super-secret-value'], $config->httpAuth());
  }

  public function testPerEnvironmentKeyTakesPrecedenceOverBareKey(): void {
    putenv('ACSF_API_USER=pfrilling');
    putenv('ACSF_API_KEY=stage-key');
    putenv('ACSF_API_KEY_PROD=prod-only-key');

    $prodConfig = FactoryConfig::fromEnvironment(FactoryEnvironment::Prod);
    $this->assertSame('prod-only-key', $prodConfig->httpAuth()[1]);

    $testConfig = FactoryConfig::fromEnvironment(FactoryEnvironment::Test);
    $this->assertSame('stage-key', $testConfig->httpAuth()[1]);
  }

  public function testFactoryUrlOverride(): void {
    putenv('ACSF_API_USER=pfrilling');
    putenv('ACSF_API_KEY=stage-key');
    putenv('ACSF_FACTORY_URL=https://custom.example.com/api/v1/');

    $config = FactoryConfig::fromEnvironment(FactoryEnvironment::Test);

    $this->assertSame('https://custom.example.com/api/v1/', $config->apiBaseUri);
  }

  public function testDefaultRequestTimeout(): void {
    putenv('ACSF_API_USER=pfrilling');
    putenv('ACSF_API_KEY=stage-key');

    $config = FactoryConfig::fromEnvironment(FactoryEnvironment::Test);

    $this->assertSame(30, $config->requestTimeoutSeconds);
  }

}

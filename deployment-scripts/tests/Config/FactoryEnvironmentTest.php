<?php

declare(strict_types=1);

namespace Ecms\Deployment\Tests\Config;

use Ecms\Deployment\Config\FactoryEnvironment;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

final class FactoryEnvironmentTest extends TestCase {

  /**
   * Builds the minimal --env/--prod input every AbstractAcsfCommand exposes.
   */
  private function inputFor(array $parameters): ArrayInput {
    $definition = new InputDefinition([
      new InputOption('env', NULL, InputOption::VALUE_REQUIRED),
      new InputOption('prod', NULL, InputOption::VALUE_NONE),
    ]);
    return new ArrayInput($parameters, $definition);
  }

  public function testNoFlagsDefaultsToTest(): void {
    $this->assertSame(
      FactoryEnvironment::Test,
      FactoryEnvironment::fromInput($this->inputFor([]))
    );
  }

  public function testProdFlagResolvesToProd(): void {
    $this->assertSame(
      FactoryEnvironment::Prod,
      FactoryEnvironment::fromInput($this->inputFor(['--prod' => TRUE]))
    );
  }

  public function testEnvDevResolvesToDev(): void {
    $this->assertSame(
      FactoryEnvironment::Dev,
      FactoryEnvironment::fromInput($this->inputFor(['--env' => 'dev']))
    );
  }

  public function testUnknownEnvThrows(): void {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Unknown environment "bogus". Valid values are: dev, test, prod.');
    FactoryEnvironment::fromInput($this->inputFor(['--env' => 'bogus']));
  }

  public function testConflictingProdAndEnvThrows(): void {
    $this->expectException(InvalidOptionException::class);
    FactoryEnvironment::fromInput($this->inputFor(['--prod' => TRUE, '--env' => 'test']));
  }

  public function testProdFlagWithMatchingEnvDoesNotConflict(): void {
    $this->assertSame(
      FactoryEnvironment::Prod,
      FactoryEnvironment::fromInput($this->inputFor(['--prod' => TRUE, '--env' => 'prod']))
    );
  }

  #[DataProvider('apiBaseUriProvider')]
  public function testApiBaseUriHosts(FactoryEnvironment $environment, string $expectedHost): void {
    $this->assertSame(sprintf('https://%s/api/v1/', $expectedHost), $environment->apiBaseUri());
  }

  public static function apiBaseUriProvider(): array {
    return [
      'dev' => [FactoryEnvironment::Dev, 'www.dev-riecms.acsitefactory.com'],
      'test' => [FactoryEnvironment::Test, 'www.test-riecms.acsitefactory.com'],
      'prod' => [FactoryEnvironment::Prod, 'www.riecms.acsitefactory.com'],
    ];
  }

}

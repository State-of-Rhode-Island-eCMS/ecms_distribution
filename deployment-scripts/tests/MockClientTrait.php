<?php

declare(strict_types=1);

namespace Ecms\Deployment\Tests;

use Ecms\Deployment\Api\AcsfClient;
use Ecms\Deployment\Config\FactoryConfig;
use Ecms\Deployment\Config\FactoryEnvironment;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Builds an AcsfClient backed entirely by a Guzzle MockHandler, via
 * AcsfClient's $handlerStack constructor seam, so tests never touch the
 * network or need real credentials.
 *
 * Requests are counted at the base handler, below AcsfClient's own retry
 * middleware, so every individual attempt is captured — including ones a
 * retry discards — not just the one final settled result a history
 * middleware pushed on top of the stack would see.
 */
trait MockClientTrait {

  /** @var RequestInterface[] */
  private array $recordedRequests = [];

  /**
   * Undoes the putenv() calls mockedClient() makes, so a test using this
   * trait can never leak fake credentials into a sibling test class (e.g.
   * FactoryConfigTest's "missing variable" assertions) run in the same
   * process. PHPUnit calls this automatically; if the using TestCase
   * defines its own tearDown(), it must call parent::tearDown() (or this
   * trait's cleanup directly) to keep that guarantee.
   */
  protected function tearDown(): void {
    foreach (['ACSF_API_USER', 'ACSF_API_KEY'] as $var) {
      putenv($var);
      unset($_ENV[$var], $_SERVER[$var]);
    }
    parent::tearDown();
  }

  private function mockedClient(MockHandler $mock, FactoryEnvironment $environment = FactoryEnvironment::Test): AcsfClient {
    putenv('ACSF_API_USER=test-user');
    putenv('ACSF_API_KEY=test-key');
    $config = FactoryConfig::fromEnvironment($environment);

    $this->recordedRequests = [];
    $countingHandler = function (RequestInterface $request, array $options) use ($mock): PromiseInterface {
      $this->recordedRequests[] = $request;
      return $mock($request, $options);
    };

    $handlerStack = HandlerStack::create($countingHandler);

    return new AcsfClient($config, NULL, $handlerStack);
  }

  /**
   * @return string[]
   */
  private function recordedMethods(): array {
    return array_map(
      static fn (RequestInterface $request): string => $request->getMethod(),
      $this->recordedRequests
    );
  }

}

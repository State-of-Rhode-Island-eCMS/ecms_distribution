<?php

declare(strict_types=1);

namespace Ecms\Deployment\Api;

use Ecms\Deployment\Api\Exception\AcsfApiException;
use Ecms\Deployment\Config\FactoryConfig;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * The one place HTTP requests to the ACSF REST API are made.
 *
 * TLS verification is always on (Guzzle's 'verify' default of TRUE is never
 * overridden here); a bad certificate is a real problem to fix, not
 * something to route around.
 */
final class AcsfClient {

  private const MAX_RETRIES = 3;

  /**
   * The underlying Guzzle client, configured with retry middleware.
   */
  private readonly Client $httpClient;

  public function __construct(
    private readonly FactoryConfig $config,
    ?string $caBundle = NULL,
    // Accepts a pre-built handler stack (e.g. wrapping a Guzzle
    // MockHandler) so tests can exercise the retry middleware below without
    // any real network access. Production code should never pass this.
    ?HandlerStack $handlerStack = NULL,
  ) {
    $handlerStack ??= HandlerStack::create();
    $handlerStack->push(Middleware::retry(
      $this->retryDecider(...),
      $this->retryDelay(...),
    ));

    $options = [
      'base_uri' => $this->config->apiBaseUri,
      'auth' => $this->config->httpAuth(),
      'timeout' => $this->config->requestTimeoutSeconds,
      'connect_timeout' => min(10, $this->config->requestTimeoutSeconds),
      // Errors are handled by the repositories via AcsfApiException, not by
      // letting Guzzle throw on 4xx/5xx; a 404 is a meaningful, expected
      // response for some endpoints (e.g. "site has no backups").
      'http_errors' => FALSE,
      'handler' => $handlerStack,
    ];
    if ($caBundle !== NULL) {
      $options['verify'] = $caBundle;
    }

    $this->httpClient = new Client($options);
  }

  /**
   * Sends a request and returns the raw response.
   *
   * Callers that need a typed error should use requestOrFail() instead.
   */
  public function request(string $method, string $uri, array $options = []): ResponseInterface {
    return $this->httpClient->request($method, $uri, $options);
  }

  /**
   * Sends a request and throws AcsfApiException on a non-2xx response.
   */
  public function requestOrFail(string $method, string $uri, array $options = []): ResponseInterface {
    $response = $this->request($method, $uri, $options);
    if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
      throw AcsfApiException::fromResponse($response, $method, $uri);
    }
    return $response;
  }

  /**
   * Decides whether a request should be retried.
   */
  private function retryDecider(
    int $retries,
    RequestInterface $request,
    ?ResponseInterface $response = NULL,
    ?\Throwable $exception = NULL,
  ): bool {
    if ($retries >= self::MAX_RETRIES) {
      return FALSE;
    }
    if ($exception !== NULL) {
      return TRUE;
    }
    return $response instanceof ResponseInterface
      && ($response->getStatusCode() === 429 || $response->getStatusCode() >= 500);
  }

  /**
   * Computes the delay, in milliseconds, before the next retry.
   */
  private function retryDelay(int $retries, ?ResponseInterface $response = NULL): int {
    if ($response !== NULL && $response->hasHeader('Retry-After')) {
      $retryAfter = $response->getHeaderLine('Retry-After');
      if (is_numeric($retryAfter)) {
        return ((int) $retryAfter) * 1000;
      }
    }
    // Exponential backoff: 1s, 2s, 4s.
    return (int) (1000 * (2 ** ($retries - 1)));
  }

}

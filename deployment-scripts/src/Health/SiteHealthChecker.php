<?php

declare(strict_types=1);

namespace Ecms\Deployment\Health;

use Ecms\Deployment\Api\Site;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

/**
 * Checks that sites are responding.
 *
 * This client is intentionally separate from AcsfClient: the ACSF factory
 * Basic auth credentials must never be sent to an arbitrary site domain, so
 * this class builds its own client with no 'auth' option at all.
 */
final class SiteHealthChecker {

  /**
   * The underlying Guzzle client, deliberately built with no 'auth' option.
   */
  private readonly Client $httpClient;

  public function __construct(
    private readonly int $timeoutSeconds = 15,
    private readonly int $concurrency = 10,
    ?string $caBundle = NULL,
    // Accepts a pre-built handler (e.g. a Guzzle MockHandler) so tests can
    // exercise pooling/concurrency without any real network access.
    // Production code should never pass this.
    HandlerStack|callable|null $handler = NULL,
  ) {
    $options = [
      'timeout' => $this->timeoutSeconds,
      'connect_timeout' => min(10, $this->timeoutSeconds),
      'http_errors' => FALSE,
      // 2xx/3xx both count as healthy (see checkAll()); redirects are
      // followed so a scheme/www redirect isn't mistaken for an outage.
      'allow_redirects' => TRUE,
    ];
    if ($caBundle !== NULL) {
      $options['verify'] = $caBundle;
    }
    if ($handler !== NULL) {
      $options['handler'] = $handler;
    }

    $this->httpClient = new Client($options);
  }

  /**
   * Checks every given site concurrently.
   *
   * @param \Ecms\Deployment\Api\Site[] $sites
   *   The sites to check.
   * @param string $path
   *   The path to request on each site.
   *
   * @return \Ecms\Deployment\Health\SiteHealthResult[]
   *   One result per site, in the same order they were given.
   */
  public function checkAll(array $sites, string $path = '/user'): array {
    $results = [];

    $urlFor = static fn (Site $site): string => sprintf('https://%s%s', $site->domain, $path);

    $requests = static function () use ($sites, $urlFor): iterable {
      foreach ($sites as $index => $site) {
        yield $index => new Request('GET', $urlFor($site));
      }
    };

    $pool = new Pool($this->httpClient, $requests(), [
      'concurrency' => $this->concurrency,
      'fulfilled' => static function (ResponseInterface $response, $index) use ($sites, $urlFor, &$results): void {
        $statusCode = $response->getStatusCode();
        $results[$index] = new SiteHealthResult(
          $sites[$index],
          $urlFor($sites[$index]),
          $statusCode >= 200 && $statusCode < 400,
          $statusCode,
          NULL,
        );
      },
      'rejected' => static function (\Throwable $reason, $index) use ($sites, $urlFor, &$results): void {
        $results[$index] = new SiteHealthResult(
          $sites[$index],
          $urlFor($sites[$index]),
          FALSE,
          NULL,
          $reason->getMessage(),
        );
      },
    ]);

    $pool->promise()->wait();

    ksort($results);
    return array_values($results);
  }

}

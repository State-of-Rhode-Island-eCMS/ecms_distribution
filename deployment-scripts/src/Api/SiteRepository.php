<?php

declare(strict_types=1);

namespace Ecms\Deployment\Api;

/**
 * Lists sites known to an ACSF factory.
 */
final class SiteRepository {

  private const LIST_LIMIT = 100;

  public function __construct(private readonly AcsfClient $client) {}

  /**
   * Streams every site in the factory, following pagination.
   *
   * @return iterable<Site>
   *   Every site in the factory.
   */
  public function all(): iterable {
    $page = 1;

    do {
      $uri = sprintf('sites?limit=%d&page=%d', self::LIST_LIMIT, $page);
      $response = $this->client->requestOrFail('GET', $uri);

      $body = json_decode((string) $response->getBody(), FALSE);
      foreach ($body->sites ?? [] as $site) {
        yield Site::fromApiResponse($site);
      }

      $hasNextPage = LinkHeader::hasNextPage($response->getHeader('Link'));
      $page++;
    } while ($hasNextPage);
  }

  /**
   * Looks up a single site by ID.
   *
   * Used to resolve an explicitly-provided --sites list into full Site
   * objects (with domain), and doubles as ID validation: a nonexistent site
   * ID surfaces as an AcsfApiException before any destructive action runs.
   */
  public function find(int $siteId): Site {
    $response = $this->client->requestOrFail('GET', sprintf('sites/%d', $siteId));
    $body = json_decode((string) $response->getBody(), FALSE);
    return Site::fromApiResponse($body->site ?? $body);
  }

}

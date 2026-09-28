<?php

declare(strict_types=1);

namespace Ecms\Deployment\Api;

/**
 * Reads pagination state out of an RFC 8288 Link header.
 *
 * Used across ACSF's paginated list endpoints.
 */
final class LinkHeader {

  /**
   * Determines whether a Link header advertises a rel="next" page.
   *
   * @param string[] $linkHeaderValues
   *   The raw values of a PSR-7 response's 'Link' header.
   */
  public static function hasNextPage(array $linkHeaderValues): bool {
    if ($linkHeaderValues === []) {
      return FALSE;
    }
    return str_contains($linkHeaderValues[0], 'rel="next"');
  }

}

<?php

declare(strict_types=1);

namespace Ecms\Deployment\Health;

use Ecms\Deployment\Api\Site;

/**
 * The outcome of checking a single site's health.
 */
final class SiteHealthResult {

  public function __construct(
    public readonly Site $site,
    public readonly string $url,
    public readonly bool $healthy,
    public readonly ?int $statusCode,
    public readonly ?string $error,
  ) {}

}

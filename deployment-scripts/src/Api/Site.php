<?php

declare(strict_types=1);

namespace Ecms\Deployment\Api;

/**
 * A single site within an ACSF factory.
 */
final class Site {

  public function __construct(
    public readonly int $id,
    public readonly string $domain,
    public readonly ?string $siteName = NULL,
  ) {}

  /**
   * Builds a Site from a decoded ACSF "site" API response object.
   */
  public static function fromApiResponse(object $data): self {
    return new self(
      (int) $data->id,
      (string) $data->domain,
      isset($data->site) ? (string) $data->site : NULL,
    );
  }

}

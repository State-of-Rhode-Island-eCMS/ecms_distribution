<?php

declare(strict_types=1);

namespace Ecms\Deployment\Api;

/**
 * A single backup belonging to a site within an ACSF factory.
 */
final class Backup {

  public function __construct(
    public readonly int $id,
    public readonly string $label,
    public readonly int $timestamp,
  ) {}

  /**
   * Builds a Backup from a decoded ACSF "backup" API response object.
   */
  public static function fromApiResponse(object $data): self {
    return new self(
      (int) $data->id,
      (string) $data->label,
      (int) $data->timestamp,
    );
  }

}

<?php

declare(strict_types=1);

namespace Ecms\Deployment\Config;

/**
 * Wraps a secret so it can be held on an object without ever printing itself.
 *
 * Covers var_dump(), print_r(), string interpolation, and any exception
 * stack trace that captures constructor arguments (both var_dump() and
 * print_r() honour __debugInfo()). Call reveal() explicitly at the one call
 * site that actually needs the raw value (building the HTTP auth option);
 * everywhere else, let it redact.
 *
 * This does NOT cover var_export(), which PHP always renders from raw
 * property state; nothing in this codebase calls var_export() on a value
 * holding a secret, and it should stay that way.
 */
final class RedactedSecret {

  public function __construct(private readonly string $value) {}

  /**
   * Returns the raw secret value.
   */
  public function reveal(): string {
    return $this->value;
  }

  /**
   * Redacts the secret when cast to a string.
   */
  public function __toString(): string {
    return '***redacted***';
  }

  /**
   * Redacts the secret in var_dump()/print_r() output.
   */
  public function __debugInfo(): array {
    return ['value' => '***redacted***'];
  }

}

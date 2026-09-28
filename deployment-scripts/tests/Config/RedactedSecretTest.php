<?php

declare(strict_types=1);

namespace Ecms\Deployment\Tests\Config;

use Ecms\Deployment\Config\RedactedSecret;
use PHPUnit\Framework\TestCase;

final class RedactedSecretTest extends TestCase {

  public function testRevealReturnsTheRawValue(): void {
    $secret = new RedactedSecret('raw-value');
    $this->assertSame('raw-value', $secret->reveal());
  }

  public function testToStringIsRedacted(): void {
    $secret = new RedactedSecret('raw-value');
    $this->assertSame('***redacted***', (string) $secret);
  }

  public function testDebugInfoIsRedacted(): void {
    $secret = new RedactedSecret('raw-value');
    $dumped = print_r($secret, TRUE);
    $this->assertStringNotContainsString('raw-value', $dumped);
  }

}

<?php

declare(strict_types=1);

namespace Ecms\Deployment\Tests\Api;

use Ecms\Deployment\Api\LinkHeader;
use PHPUnit\Framework\TestCase;

final class LinkHeaderTest extends TestCase {

  public function testEmptyHeaderHasNoNextPage(): void {
    $this->assertFalse(LinkHeader::hasNextPage([]));
  }

  public function testNextRelIsDetected(): void {
    $this->assertTrue(LinkHeader::hasNextPage(['<https://x/?page=2>; rel="next"']));
  }

  public function testOtherRelIsNotNextPage(): void {
    $this->assertFalse(LinkHeader::hasNextPage(['<https://x/?page=1>; rel="prev"']));
  }

}

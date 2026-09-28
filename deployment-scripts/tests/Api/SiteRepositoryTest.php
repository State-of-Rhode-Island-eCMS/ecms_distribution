<?php

declare(strict_types=1);

namespace Ecms\Deployment\Tests\Api;

use Ecms\Deployment\Api\SiteRepository;
use Ecms\Deployment\Tests\MockClientTrait;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class SiteRepositoryTest extends TestCase {

  use MockClientTrait;

  public function testAllFollowsRelNextAcrossThreePagesAndStops(): void {
    $mock = new MockHandler([
      new Response(200, ['Link' => '<https://x/sites?page=2>; rel="next"'], json_encode([
        'sites' => [['id' => 1, 'domain' => 'one.example.com']],
      ])),
      new Response(200, ['Link' => '<https://x/sites?page=3>; rel="next"'], json_encode([
        'sites' => [['id' => 2, 'domain' => 'two.example.com']],
      ])),
      new Response(200, [], json_encode([
        'sites' => [['id' => 3, 'domain' => 'three.example.com']],
      ])),
    ]);

    $repository = new SiteRepository($this->mockedClient($mock));
    $sites = iterator_to_array($repository->all(), FALSE);

    $this->assertCount(3, $sites);
    $this->assertSame([1, 2, 3], array_map(static fn ($s) => $s->id, $sites));
    $this->assertSame(
      ['one.example.com', 'two.example.com', 'three.example.com'],
      array_map(static fn ($s) => $s->domain, $sites)
    );
    // Exactly 3 requests were made; the loop stopped once rel="next" was absent.
    $this->assertCount(3, $this->recordedMethods());
  }

  public function testFindReturnsSingleSite(): void {
    $mock = new MockHandler([
      new Response(200, [], json_encode(['id' => 42, 'domain' => 'found.example.com'])),
    ]);

    $repository = new SiteRepository($this->mockedClient($mock));
    $site = $repository->find(42);

    $this->assertSame(42, $site->id);
    $this->assertSame('found.example.com', $site->domain);
  }

}

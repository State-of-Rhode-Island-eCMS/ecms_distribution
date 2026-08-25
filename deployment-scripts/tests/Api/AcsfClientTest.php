<?php

declare(strict_types=1);

namespace Ecms\Deployment\Tests\Api;

use Ecms\Deployment\Api\Exception\AcsfApiException;
use Ecms\Deployment\Tests\MockClientTrait;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class AcsfClientTest extends TestCase {

  use MockClientTrait;

  public function testRetriesOnceOn429ThenSucceeds(): void {
    $mock = new MockHandler([
      new Response(429, ['Retry-After' => '0']),
      new Response(200, [], 'ok'),
    ]);

    $client = $this->mockedClient($mock);
    $response = $client->requestOrFail('GET', 'sites');

    $this->assertSame(200, $response->getStatusCode());
    $this->assertCount(2, $this->recordedMethods());
  }

  public function testGivesUpAfterMaxRetries(): void {
    $mock = new MockHandler([
      new Response(429, ['Retry-After' => '0']),
      new Response(429, ['Retry-After' => '0']),
      new Response(429, ['Retry-After' => '0']),
      new Response(429, ['Retry-After' => '0']),
    ]);

    $client = $this->mockedClient($mock);

    $this->expectException(AcsfApiException::class);
    try {
      $client->requestOrFail('GET', 'sites');
    }
    finally {
      // The initial attempt plus 3 retries: 4 requests total, then it gives up.
      $this->assertCount(4, $this->recordedMethods());
    }
  }

  public function testRetriesOn5xx(): void {
    $mock = new MockHandler([
      new Response(503, ['Retry-After' => '0']),
      new Response(200, [], 'ok'),
    ]);

    $client = $this->mockedClient($mock);
    $response = $client->requestOrFail('GET', 'sites');

    $this->assertSame(200, $response->getStatusCode());
  }

  public function testDoesNotRetryOn404(): void {
    $mock = new MockHandler([
      new Response(404),
    ]);

    $client = $this->mockedClient($mock);

    try {
      $client->requestOrFail('GET', 'sites/1/backups');
      $this->fail('Expected AcsfApiException.');
    }
    catch (AcsfApiException $e) {
      $this->assertSame(404, $e->statusCode);
    }
    $this->assertCount(1, $this->recordedMethods());
  }

}

<?php

declare(strict_types=1);

namespace Ecms\Deployment\Tests\Health;

use Ecms\Deployment\Api\Site;
use Ecms\Deployment\Health\SiteHealthChecker;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class SiteHealthCheckerTest extends TestCase {

  public function testNeverSendsAnAuthorizationHeader(): void {
    $mock = new MockHandler([new Response(200), new Response(200)]);
    $handlerStack = HandlerStack::create($mock);

    /** @var RequestInterface[] $seen */
    $seen = [];
    $handlerStack->push(Middleware::tap(function (RequestInterface $request) use (&$seen): void {
      $seen[] = $request;
    }));

    $checker = new SiteHealthChecker(handler: $handlerStack);
    $checker->checkAll([new Site(1, 'a.example.com'), new Site(2, 'b.example.com')]);

    $this->assertNotEmpty($seen);
    foreach ($seen as $request) {
      $this->assertFalse($request->hasHeader('Authorization'));
    }
  }

  public function testClientNeverDisablesTlsVerification(): void {
    $checker = new SiteHealthChecker();

    $client = (new \ReflectionProperty(SiteHealthChecker::class, 'httpClient'))->getValue($checker);
    $config = (new \ReflectionProperty(\GuzzleHttp\Client::class, 'config'))->getValue($client);

    $this->assertArrayNotHasKey('auth', $config);
    $this->assertNotSame(FALSE, $config['verify'] ?? TRUE);
  }

  public function testHealthyOn2xx(): void {
    $mock = new MockHandler([new Response(200)]);
    $checker = new SiteHealthChecker(handler: HandlerStack::create($mock));

    $results = $checker->checkAll([new Site(1, 'a.example.com')]);

    $this->assertTrue($results[0]->healthy);
    $this->assertSame(200, $results[0]->statusCode);
  }

  public function testUnhealthyOn404(): void {
    $mock = new MockHandler([new Response(404)]);
    $checker = new SiteHealthChecker(handler: HandlerStack::create($mock));

    $results = $checker->checkAll([new Site(1, 'a.example.com')]);

    $this->assertFalse($results[0]->healthy);
    $this->assertSame(404, $results[0]->statusCode);
  }

  public function testRedirectIsFollowedToTheEventualStatus(): void {
    // allow_redirects is on, so a 301 is never the *final* status seen —
    // Guzzle transparently follows it to the redirect target's response.
    // A single site is used here since a mock queue does not line up
    // predictably with per-site order once concurrent redirects are
    // interleaved with a Pool of unrelated requests.
    $mock = new MockHandler([
      new Response(301, ['Location' => 'https://a.example.com/user']),
      new Response(200),
    ]);
    $checker = new SiteHealthChecker(handler: HandlerStack::create($mock));

    $results = $checker->checkAll([new Site(1, 'a.example.com')]);

    $this->assertTrue($results[0]->healthy);
    $this->assertSame(200, $results[0]->statusCode);
  }

  public function testConnectionFailureIsUnhealthyWithError(): void {
    $mock = new MockHandler([
      new \GuzzleHttp\Exception\ConnectException(
        'Could not resolve host',
        new \GuzzleHttp\Psr7\Request('GET', 'https://down.example.com/user')
      ),
    ]);
    $checker = new SiteHealthChecker(handler: HandlerStack::create($mock));

    $results = $checker->checkAll([new Site(1, 'down.example.com')]);

    $this->assertFalse($results[0]->healthy);
    $this->assertNull($results[0]->statusCode);
    $this->assertStringContainsString('Could not resolve host', $results[0]->error);
  }

}

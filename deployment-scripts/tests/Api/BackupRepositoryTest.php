<?php

declare(strict_types=1);

namespace Ecms\Deployment\Tests\Api;

use Ecms\Deployment\Api\BackupRepository;
use Ecms\Deployment\Tests\MockClientTrait;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class BackupRepositoryTest extends TestCase {

  use MockClientTrait;

  public function testForSitePaginatesPastASinglePage(): void {
    $mock = new MockHandler([
      new Response(200, ['Link' => '<https://x/sites/1/backups?page=2>; rel="next"'], json_encode([
        'backups' => [
          ['id' => 1, 'label' => 'a', 'timestamp' => 1],
          ['id' => 2, 'label' => 'b', 'timestamp' => 2],
        ],
      ])),
      new Response(200, [], json_encode([
        'backups' => [
          ['id' => 3, 'label' => 'c', 'timestamp' => 3],
        ],
      ])),
    ]);

    $repository = new BackupRepository($this->mockedClient($mock));
    $backups = iterator_to_array($repository->forSite(1), FALSE);

    $this->assertCount(3, $backups);
    $this->assertSame([1, 2, 3], array_map(static fn ($b) => $b->id, $backups));
  }

  public function testCreateReturnsTaskId(): void {
    $mock = new MockHandler([
      new Response(200, [], json_encode(['task_id' => 999])),
    ]);

    $repository = new BackupRepository($this->mockedClient($mock));
    $taskId = $repository->create(1, ['database']);

    $this->assertSame(999, $taskId);
    $this->assertSame(['POST'], $this->recordedMethods());
  }

  public function testDeleteReturnsTaskId(): void {
    $mock = new MockHandler([
      new Response(200, [], json_encode(['task_id' => 888])),
    ]);

    $repository = new BackupRepository($this->mockedClient($mock));
    $taskId = $repository->delete(1, 5);

    $this->assertSame(888, $taskId);
    $this->assertSame(['DELETE'], $this->recordedMethods());
  }

}

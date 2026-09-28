<?php

declare(strict_types=1);

namespace Ecms\Deployment\Api;

/**
 * Creates, lists, and deletes backups for sites within an ACSF factory.
 */
final class BackupRepository {

  private const LIST_LIMIT = 100;

  public function __construct(private readonly AcsfClient $client) {}

  /**
   * Requests a new backup for the given site.
   *
   * @param int $siteId
   *   The ACSF site ID to back up.
   * @param string[] $components
   *   E.g. ['database']. 'codebase' is deliberately not a default component
   *   since it is identical across every site in the factory and cannot be
   *   restored into it.
   *
   * @return int
   *   The ACSF task ID for the backup job.
   */
  public function create(int $siteId, array $components): int {
    $response = $this->client->requestOrFail('POST', sprintf('sites/%d/backup', $siteId), [
      'form_params' => ['components' => $components],
    ]);
    $body = json_decode((string) $response->getBody(), FALSE);
    return (int) $body->task_id;
  }

  /**
   * Streams every backup for the given site, following pagination.
   *
   * @return iterable<Backup>
   *   The site's backups, oldest and newest alike.
   */
  public function forSite(int $siteId): iterable {
    $page = 1;

    do {
      $uri = sprintf('sites/%d/backups?limit=%d&page=%d', $siteId, self::LIST_LIMIT, $page);
      $response = $this->client->requestOrFail('GET', $uri);

      $body = json_decode((string) $response->getBody(), FALSE);
      foreach ($body->backups ?? [] as $backup) {
        yield Backup::fromApiResponse($backup);
      }

      $hasNextPage = LinkHeader::hasNextPage($response->getHeader('Link'));
      $page++;
    } while ($hasNextPage);
  }

  /**
   * Deletes a backup, returning the ACSF task ID for the deletion job.
   */
  public function delete(int $siteId, int $backupId): int {
    $response = $this->client->requestOrFail(
      'DELETE',
      sprintf('sites/%d/backups/%d', $siteId, $backupId)
    );
    $body = json_decode((string) $response->getBody(), FALSE);
    return (int) $body->task_id;
  }

}

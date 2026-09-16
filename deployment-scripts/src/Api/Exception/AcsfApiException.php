<?php

declare(strict_types=1);

namespace Ecms\Deployment\Api\Exception;

use GuzzleHttp\Psr7\Message;
use Psr\Http\Message\ResponseInterface;

/**
 * Thrown when the ACSF API responds with a non-2xx status.
 *
 * Never include the request's Authorization header in the exception
 * message; the message is what ends up in CI logs and console output.
 */
final class AcsfApiException extends \RuntimeException {

  private function __construct(
    string $message,
    public readonly int $statusCode,
    public readonly string $requestMethod,
    public readonly string $requestUri,
  ) {
    parent::__construct($message);
  }

  /**
   * Builds an exception describing a failed request/response pair.
   */
  public static function fromResponse(
    ResponseInterface $response,
    string $method,
    string $uri,
  ): self {
    $body = (string) $response->getBody();
    $decoded = json_decode($body, FALSE);
    $apiMessage = is_object($decoded) && isset($decoded->message)
      ? (string) $decoded->message
      : Message::bodySummary($response) ?? '<empty response body>';

    return new self(
      sprintf(
        '%s %s returned HTTP %d: %s',
        $method,
        $uri,
        $response->getStatusCode(),
        $apiMessage
      ),
      $response->getStatusCode(),
      $method,
      $uri,
    );
  }

}

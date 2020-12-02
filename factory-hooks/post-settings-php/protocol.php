<?php

declare(strict_types = 1);

/**
 * Test if the current request is encrypted.
 * @return bool
 */
function isSecure(): bool {
  return
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' && $_SERVER['HTTPS'] !== 'OFF')
    || $_SERVER['SERVER_PORT'] == 443;
}

// Redirect to the secure version.
if ($_ENV['AH_SITE_ENVIRONMENT'] === "prod" && !isSecure()) {
  header('HTTP/1.0 301 Moved Permanently');
  header('Location: ' . $_SERVER['REQUEST_URI']);
  exit();
}

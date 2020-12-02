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
if (($_ENV['AH_SITE_ENVIRONMENT'] === "prod" || $_ENV['AH_SITE_ENVIRONMENT'] === "01dev") && !isSecure()) {
  header('HTTP/1.0 301 Moved Permanently');
  header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
  exit();
}


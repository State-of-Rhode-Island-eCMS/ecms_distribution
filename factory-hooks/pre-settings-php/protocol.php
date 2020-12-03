<?php

declare(strict_types = 1);

/**
 * Test if the current request is encrypted.
 * @return bool
 */
function isSecure(): bool {
  $isSecure = false;
  if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    $isSecure = true;
  }
  elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
    $isSecure = true;
  }
  return $isSecure;
}

// Redirect to the secure version.
if (($_ENV['AH_SITE_ENVIRONMENT'] === "prod" || $_ENV['AH_SITE_ENVIRONMENT'] === "01dev") && !isSecure()) {
  header('HTTP/1.0 301 Moved Permanently');
  header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
  exit();
}


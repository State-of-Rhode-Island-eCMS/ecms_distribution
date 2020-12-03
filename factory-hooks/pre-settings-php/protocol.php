<?php

declare(strict_types = 1);

/**
 * Test if the current request is encrypted.
 * @return bool
 */
function isSecure(): bool {
  $isSecure = FALSE;
  if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $isSecure = TRUE;
  }
  elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') {
    $isSecure = TRUE;
  }
  return $isSecure;
}

// Redirect to the secure version.
if (($_ENV['AH_SITE_ENVIRONMENT'] === "01live" || $_ENV['AH_SITE_ENVIRONMENT'] === "01dev") && !isSecure()) {
  header('HTTP/1.0 301 Moved Permanently');
  header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
  exit();
}


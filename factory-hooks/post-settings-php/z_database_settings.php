<?php

declare(strict_types = 1);

/**
* Enable external database connection for a given Acquia environment.
 * The 3 environment variables must be defined via putenv() calls
 * in the secrets.settings.php file for each environment.
*/

if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  $databases['external']['default'] = array(
    'database' => getenv("EXTERNAL_DATABASE_NAME"),
    'username' => getenv("EXTERNAL_DATABASE_USER"),
    'password' => getenv("EXTERNAL_DATABASE_PASSWORD"),
    'prefix' => '',
    'host' => 'localhost',
    'port' => '3306',
    'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
    'driver' => 'mysql',
  );
}

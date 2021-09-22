<?php

declare(strict_types = 1);

/**
* Enable development services for DEV environment.
*/

if (isset($_ENV['AH_SITE_ENVIRONMENT']) && $_ENV['AH_SITE_ENVIRONMENT'] === '01dev') {

  $databases['external']['default'] = array(
    'database' => 'riecms01ddb1046451',
    'username' => 'MnrWrAD3LTXCde6U',
    'password' => getenv("DATABASE_POC_PASSWORD"),
    'prefix' => '',
    'host' => 'riecms01dev.ssh.enterprise-g1.acquia-sites.com',
    'port' => '3306',
    'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
    'driver' => 'mysql',
  );
}

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
//    'init_commands' => [
//      'isolation_level' => 'SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED',
//    ],
  );

//  $databases['default']['default']['init_commands'] = [
//    'isolation_level' => 'SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED',
//  ];

//  // Set DB transaction isolation level to 'READ COMMITTED' on Acquia environments.
//  // Mysql/MariaDB default to the 'REPEATABLE READ' setting, but it can result in deadlocks.
//  // See: https://www.drupal.org/docs/getting-started/system-requirements/setting-the-mysql-transaction-isolation-level.
//  if (file_exists('/var/www/site-php')) {
//    global $conf, $databases;
//    $conf['acquia_hosting_settings_autoconnect'] = FALSE;
//
//    // Get drupal version and site settings from active environment.
//    // These are needed to generate file path to the 'settings.inc' file.
//    // Approach borrowed from 'modules/contrib/acsf/acsf_init/lib/sites/g/settings.php'.
//    $drupal_version = 8;
//    if (version_compare(\Drupal::VERSION, '9', '>=') && version_compare(\Drupal::VERSION, '10', '<')) {
//      $drupal_version = 9;
//    }
//    elseif (version_compare(\Drupal::VERSION, '10', '>=')) {
//      $drupal_version = 10;
//    }
//    $site_settings = !empty($GLOBALS['gardens_site_settings'])
//      ? $GLOBALS['gardens_site_settings']
//      : ['site' => '', 'env' => '', 'conf' => ['acsf_db_name' => '']];
//
//    // Generate file path, and require settings file.
//    $_acsf_include_file = "/var/www/site-php/{$site_settings['site']}.{$site_settings['env']}/D{$drupal_version}-{$site_settings['env']}-{$site_settings['conf']['acsf_db_name']}-settings.inc";
//    require $_acsf_include_file;
//
//    $databases['default']['default']['init_commands'] = [
//      'isolation_level' => "SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED",
//    ];
//    if (function_exists("acquia_hosting_db_choose_active")) {
//      acquia_hosting_db_choose_active();
//    }
//  }

}



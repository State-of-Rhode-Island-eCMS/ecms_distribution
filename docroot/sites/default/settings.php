<?php

// ===== Added by acsf-init, please do not delete. Section start. =====
$_acsf_infrastructure = include dirname(__FILE__) . '/acsf.settings.php';
if ($_acsf_infrastructure === 'acsf-infrastructure') {
  return;
}
// ===== Added by acsf-init, please do not delete. Section end. =====

use Drupal\Core\Installer\InstallerKernel;

/**
 * Load local development override configuration, if available.
 *
 * Use settings.local.php to override variables on secondary (staging,
 * development, etc) installations of this site. Typically used to disable
 * caching, JavaScript/CSS compression, re-routing of outgoing emails, and
 * other things that should not happen on development and testing sites.
 *
 * Keep this code block at the end of this file to take full effect.
 */
if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings.local.php';
}

// Lando environment settings
if (getenv('LANDO_INFO')) {
  $lando_info = json_decode(getenv('LANDO_INFO'), TRUE);

  // Files directory paths.
  $settings['file_public_path'] = 'sites/default/files';
  $settings['file_private_path'] = 'sites/default/files/private';

  $settings['config_sync_directory'] = '/app/config/common';
  $settings['file_temp_path'] = '/tmp';

  // Generic hash salt for all local environments.
  $settings['hash_salt'] = 'BfHE?EG)vJPa3uikBCZWW#ATbDLijMFRZgfkyayYcZYoy>eC7QhdG7qaB4hcm4x$';

  // Allow any domains to access the site with Lando.
  $settings['trusted_host_patterns'] = [
    '^(.+)$',
  ];

  // Enable Configuration Read-only Mode (Only on Prod & UAT)
  if (PHP_SAPI !== 'cli') {
    $settings['config_readonly'] = TRUE;
  }

  // Add default config split settings for local development.
  $config['config_split.config_split.local']['status'] = TRUE;
  $config['config_split.config_split.dev']['status'] = FALSE;
  $config['config_split.config_split.uat']['status'] = FALSE;
  $config['config_split.config_split.prod']['status'] = FALSE;

  $databases['default']['default'] = [
    'database' => $lando_info['database']['creds']['database'],
    'username' => $lando_info['database']['creds']['user'],
    'password' => $lando_info['database']['creds']['password'],
    'prefix' => '',
    'host' => $lando_info['database']['internal_connection']['host'],
    'port' => $lando_info['database']['internal_connection']['port'],
    'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
    'driver' => 'mysql',
    'init_commands' => [
      'isolation_level' => 'SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED',
    ],
  ];

  // Check for PHP Memcached libraries.
  $memcache_exists = class_exists('Memcache', FALSE);
  $memcached_exists = class_exists('Memcached', FALSE);
  $memcache_module_is_present = file_exists(DRUPAL_ROOT . '/modules/contrib/memcache/memcache.services.yml');
  if ($memcache_module_is_present && ($memcache_exists || $memcached_exists)) {
    $settings['memcache']['servers'] = ['cache:11211' => 'default'];
    $settings['memcache']['bins'] = ['default' => 'default'];
    $settings['memcache']['key_prefix'] = 'ecms_';

    if (!InstallerKernel::installationAttempted()) {
      $settings['cache']['default'] = 'cache.backend.memcache';
    }
  }
}

// Set DB transaction isolation level to 'READ COMMITTED' on Acquia environments.
// Mysql/MariaDB default to 'REPEATABLE READ', but it can result in deadlocks.
// See: https://www.drupal.org/docs/getting-started/system-requirements/setting-the-mysql-transaction-isolation-level.
//if (file_exists('/var/www/site-php')) {
//  global $conf, $databases;
//  $conf['acquia_hosting_settings_autoconnect'] = FALSE;
//  // EDIT next line to proper path to include file.
//
//  // @TO-DO: this will be a problem on site factory,
//  // Not sure how to address multisite.
//  require('/var/www/site-php/MYSITE/MYDATABASE-settings.inc');
//
//  $databases['default']['default']['init_commands'] = [
//    'isolation_level' => "SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED",
//  ];
//  acquia_hosting_db_choose_active();
//}

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

  $settings['state_cache'] = TRUE;

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

  require DRUPAL_ROOT . '/modules/contrib/mysql57/settings.inc';

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

// Automatically generated include for settings managed by ddev.
$ddev_settings = dirname(__FILE__) . '/settings.ddev.php';
if (getenv('IS_DDEV_PROJECT') == 'true' && is_readable($ddev_settings)) {
  require $ddev_settings;
}

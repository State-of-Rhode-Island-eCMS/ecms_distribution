<?php

// ===== Added by acsf-init, please do not delete. Section start. =====
$_acsf_infrastructure = include dirname(__FILE__) . '/acsf.settings.php';
if ($_acsf_infrastructure === 'acsf-infrastructure') {
  return;
}
// ===== Added by acsf-init, please do not delete. Section end. =====

use Drupal\Core\Installer\InstallerKernel;

// Define the project root as a constant to be used later.
defined('PROJECT_ROOT') || define('PROJECT_ROOT', dirname(__DIR__, 3));

// Drupal 11 requires the state cache.
$settings['state_cache'] = TRUE;

// Define the file directories.
// Files directory paths.
$settings['file_public_path'] = 'sites/default/files';
// Private files directory path. Uncomment this and set for hosting environment.
//$settings['file_private_path'] = 'sites/default/files/private';

// Automatically generated include for settings managed by ddev.
$ddev_settings = dirname(__FILE__) . '/settings.ddev.php';
if (getenv('IS_DDEV_PROJECT') == 'true' && is_readable($ddev_settings)) {
  // Define the private file directory for the local project.
  $settings['file_private_path'] = PROJECT_ROOT . '/private/files';

  require $ddev_settings;

  // Enable memcache servers for ddev. This will be automatically
  // set for the Acquia environments.
  $settings['memcache']['servers'] = ['memcached:11211' => 'default'];

  // SOLR override for local development.
  $config['search_api.server.acquia_search_server']['backend'] = 'search_api_solr';
  $config['search_api.server.acquia_search_server']['backend_config']['connector'] = 'solr_cloud_basic_auth';
  $config['search_api.server.acquia_search_server']['backend_config']['connector_config']['scheme'] = 'http';
  $config['search_api.server.acquia_search_server']['backend_config']['connector_config']['host'] = 'solr';
  $config['search_api.server.acquia_search_server']['backend_config']['connector_config']['core'] = 'ecms';
  $config['search_api.server.acquia_search_server']['backend_config']['connector_config']['context'] = 'solr';
  $config['search_api.server.acquia_search_server']['backend_config']['connector_config']['username'] = 'solr';
  $config['search_api.server.acquia_search_server']['backend_config']['connector_config']['password'] = 'SolrRocks';

}

// Memcached settings.
$memcacheSettings = sprintf('%s/factory-hooks/post-settings-php/acsfd8+.memcache.settings.php', PROJECT_ROOT);
if (file_exists($memcacheSettings)) {
  require($memcacheSettings);
}

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

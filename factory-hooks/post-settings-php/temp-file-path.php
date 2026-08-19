<?php

declare(strict_types = 1);

/**
 *  Set temp file path for the Site Factory environment.
 */

// Setting from Acquia documentation:
// 'https://acquia.my.site.com/s/article/360042658553-Fix-for-the-warning-about-the-Drupal-8-8-x-deprecated-temporary-directory-configuration'.
$settings['file_temp_path'] = '/mnt/tmp/' . $_ENV['AH_SITE_GROUP'] . '.' . $_ENV['AH_SITE_ENVIRONMENT'];

// Setting from workaround in Feeds issue: 'https://www.drupal.org/project/feeds/issues/2912130'.
$config['system.file']['path']['temporary'] = '/mnt/tmp/' . $_ENV['AH_SITE_GROUP'] . '.' . $_ENV['AH_SITE_ENVIRONMENT'];

// Use private file system for webform exports to ensure files are accessible
// across all cluster nodes on ACSF. Without this, PDF files are written to the
// local /tmp on one server and missing when subsequent batch requests hit a
// different server. See: https://www.drupal.org/project/webform/issues/3284953
//
// Webform hands this value straight to fopen() and to ZipArchive::open(), and
// ZipArchive cannot read stream wrappers, so this must be a real filesystem
// path rather than a 'private://' URI.
//
// ACSF sets file_private_path per site before the post-settings-php hooks run.
// Fall back to rebuilding the path from the site's ACSF database name, which
// is the per-site directory name under sites/g/files-private.
$ecms_private_path = $settings['file_private_path'] ?? '';

if (empty($ecms_private_path) && !empty($GLOBALS['gardens_site_settings']['conf']['acsf_db_name'])) {
  $ecms_private_path = sprintf(
    '/mnt/files/%s.%s/sites/g/files-private/%s',
    $_ENV['AH_SITE_GROUP'],
    $_ENV['AH_SITE_ENVIRONMENT'],
    $GLOBALS['gardens_site_settings']['conf']['acsf_db_name']
  );
}

if (!empty($ecms_private_path)) {
  $config['webform.settings']['export']['temp_directory'] = rtrim($ecms_private_path, '/') . '/webform_exports';
}

unset($ecms_private_path);

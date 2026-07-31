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
$config['webform.settings']['export']['temp_directory'] = 'private://webform_exports';

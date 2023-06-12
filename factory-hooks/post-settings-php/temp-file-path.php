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

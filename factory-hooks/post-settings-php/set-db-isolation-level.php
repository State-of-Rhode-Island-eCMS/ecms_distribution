<?php

/**
 * @file
 * Set the database transaction isolation level to `READ-COMMITTED`.
 *
 * @see https://support-acquia.force.com/s/article/360005253954-Fixing-database-deadlocks
 * @see https://www.drupal.org/docs/getting-started/system-requirements/setting-the-mysql-transaction-isolation-level
 */

// Set DB transaction isolation level to 'READ COMMITTED' on Acquia environments.
// Mysql and MariaDB use default setting 'REPEATABLE READ', but it can result in deadlocks.
$databases['default']['default']['init_commands'] = [
  'isolation_level' => "SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED",
];
if (file_exists('/var/www/site-php') && function_exists("acquia_hosting_db_choose_active")) {
  acquia_hosting_db_choose_active($conf['acquia_hosting_site_info']['db'], 'default', $databases, $conf);
}

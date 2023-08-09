<?php

/**
 * @file
 * Example implementation of ACSF post-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/extend/hooks
 */

// Changing the database transaction isolation level from `REPEATABLE-READ`
// to `READ-COMMITTED` to avoid/minimize the deadlocks.
// @see https://support-acquia.force.com/s/article/360005253954-Fixing-database-deadlocks
// for reference.

$databases['default']['default']['init_commands'] = [
  'isolation_level' => "SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED",
];
if (file_exists('/var/www/site-php') && function_exists("acquia_hosting_db_choose_active")) {
  acquia_hosting_db_choose_active($conf['acquia_hosting_site_info']['db'], 'default', $databases, $conf);
}

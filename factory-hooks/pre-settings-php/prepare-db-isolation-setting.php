<?php

/**
 * @file
 * Prepare environment to update DB transaction isolation level.
 *
 * @see https://docs.acquia.com/site-factory/extend/hooks/settings-php/#avoiding-database-deadlocks
 */

// Prevent auto-connecting to DB, so we can alter settings first.
$conf['acquia_hosting_settings_autoconnect'] = FALSE;

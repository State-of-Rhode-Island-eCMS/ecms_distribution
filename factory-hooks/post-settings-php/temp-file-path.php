<?php

declare(strict_types = 1);

/**
 *  Set temp file path for the Site Factory environment.
 */

$settings['file_temp_path'] = '/mnt/tmp/' . $_ENV['AH_SITE_GROUP'] . '.' . $_ENV['AH_SITE_ENVIRONMENT'];

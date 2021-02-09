<?php

declare(strict_types = 1);

/**
* Enable development services for DEV environment.
*/

if (isset($_ENV['AH_SITE_ENVIRONMENT']) && $_ENV['AH_SITE_ENVIRONMENT'] === '01dev') {
  $settings['container_yamls'][] = '../services/development.services.yml';
}

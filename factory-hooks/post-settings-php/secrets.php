<?php

declare(strict_types = 1);

$secrets_file = sprintf('/mnt/files/%s.%s/secrets.settings.php', $_ENV['AH_SITE_GROUP'],$_ENV['AH_SITE_ENVIRONMENT']);

if (file_exists($secrets_file)) {
  require $secrets_file;
}

$cloudNextSecretsFile = sprintf('%s/secrets.settings.php', $_ENV['HOME']);

if (file_exists($cloudNextSecretsFile)) {
  require $cloudNextSecretsFile;
}

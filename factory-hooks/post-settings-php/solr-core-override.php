<?php

declare(strict_types = 1);

// Override Acquia Search Solr search core.
if (isset($_ENV['AH_SITE_ENVIRONMENT'])) {
  switch ($_ENV['AH_SITE_ENVIRONMENT']) {
    case '01test':
      $config['acquia_search_solr.settings']['override_search_core'] = "AMTW-199087.01test.riecms";
      break;
    case '01live':
      $config['acquia_search_solr.settings']['override_search_core'] = "AMTW-199087.01live.riecms";
      break;
    default:
      // Use the 01dev server for all others
      $config['acquia_search_solr.settings']['override_search_core'] = "AMTW-199087.01dev.riecms";
      break;
  }
}

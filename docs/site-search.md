## Site Search

Site search is available in two optional methods, database site search
and Solr search, driven by Acquia Search, using Solr 7. Each option
is made available as a feature which can be enabled manually.

## Solr Search
The Solr servers must be defined in a post-settings-php factory hook.
This is currently in place in the following file:
```
/factory-hooks/post-settings-php/solr-core-override.php
```

The Acquia Connector module, installed by default with the ecms_acquia
profile, is required to connect to the Solr servers. After installing
the site, simply enable the eCMS Acquia Solr Search feature.
This will enable Acquia Search, and install the config needed for
the search server, index, and view.

## Database Search
As a fallback for Solr, a database search and index are provided via
the eCMS Database Search feature. Simply enable this feature to install
the necessary config for this database driven search.



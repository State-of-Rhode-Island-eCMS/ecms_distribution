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

### Testing Solr Locally
In order to test Solr in a local lando environment we need to use the dev search core.

*  Navigate to `/admin/config/search/search-api/server/acquia_search_server`
*  Copy the value of dev Solr core under `Solr core(s) currently available for your application`
*  Create a settings.local.php if there isn't one already
*  Add:

```php
// Use the dev search core
$config['acquia_search.settings']['override_search_core'] = "[SEARCH-CORE-URL]";
```
* Clear caches

## Database Search
As a fallback for Solr, a database search and index are provided via
the eCMS Database Search feature. Simply enable this feature to install
the necessary config for this database driven search.



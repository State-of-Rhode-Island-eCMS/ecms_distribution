#!/bin/sh
#
# Factory Hook: post-site-duplication
#
# This script ensures that the site's Solr configuration will be unique
# after the site clone.
#
# Usage: SCRIPTNAME site env db-role domain custom-arg1 custom-arg2 ...
# Map the script inputs to convenient names.
# Acquia hosting site / environment names
site="$1"
env="$2"

# database role. (Not expected to be needed in most hook scripts.)
db_role="$3"

# The public domain name of the website.
domain="$4"

# Custom argument passed with the code update UI.
# Custom argument.
# custom_argument="$5"

# The websites' document root can be derived from the site/env:
docroot="/var/www/html/$site.$env/docroot"

# Acquia recommends the following two practices:
# 1. Hardcode the drush version.
# 2. When running drush, provide the application + url, rather than relying
#    on aliases. This can prevent some hard to trace problems.
DRUSH_CMD="drush10 --verbose --root=$docroot --uri=https://$domain"

# Run `drush updatedb`.
$DRUSH_CMD sdel search_api_solr.site_hash --yes  >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/post-duplication-solr-config${domain}-$(date +"%Y-%m-%d").log
$DRUSH_CMD sapi-i acquia_search_index --yes   >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/post-duplication-solr-config${domain}-$(date +"%Y-%m-%d").log
$DRUSH_CMD cr --yes >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/post-duplication-${domain}-$(date +"%Y-%m-%d").log


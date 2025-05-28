#!/bin/sh
#
# Factory Hook: post-staging-update
#
# This scrip will reset API consumer ids/secrets to the correct environment.
#
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
DRUSH_CMD="/var/www/html/$site.$env/vendor/bin/drush --verbose --root=$docroot --uri=https://$domain"

# Apply the hub connection requirements to all new sites
$DRUSH_CMD cache:rebuild --yes
$DRUSH_CMD ecms:save-publishing-consumer --yes 2>&1 | tee -a /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/post-staging-${domain}-$(date +"%Y-%m-%d").log
$DRUSH_CMD ecms:update-syndicates $env --yes 2>&1 |tee -a /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/post-staging-${domain}-$(date +"%Y-%m-%d").log
$DRUSH_CMD ecms:save-recipient-consumer  --yes 2>&1 |tee -a /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/post-staging-${domain}-$(date +"%Y-%m-%d").log
$DRUSH_CMD cache:rebuild --yes

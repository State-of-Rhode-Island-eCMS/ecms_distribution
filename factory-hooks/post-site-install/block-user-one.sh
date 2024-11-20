#!/bin/sh
#
# Factory Hook: post-site-install
#
# This script will block user/1 after the site is installed.
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
DRUSH_CMD="/var/www/html/$site.$env/vendor/bin/drush --verbose --root=$docroot --uri=https://$domain"

# Ensures the default email address is ecms@notification.ri.gov.
$DRUSH_CMD user:block --uid=1 >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/post-install-${domain}-$(date +"%Y-%m-%d").log


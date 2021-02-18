#!/bin/sh
#
# Factory Hook: db-update
#
# The existence of one or more executable files in the
# /factory-hooks/db-update directory will prompt them to be run
# *instead of* the regular database update (drush updatedb) command. So
# that update command will normally be part of the commands executed
# below.
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
$DRUSH_CMD updatedb --yes >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/drush-update.log

# Run `drush features:import:all`.
$DRUSH_CMD features:import:all --bundle=ecms --yes >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/drush-features.log

# Rebuild caches after features import.
$DRUSH_CMD cache-rebuild >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/drush-cache.log

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

# For sites without Drupal installed we can skip.
if [[ "$site" == "external.test-riecms.acsitefactory.com" ]]
then
  exit
fi

env="$2"

# database role. (Not expected to be needed in most hook scripts.)
db_role="$3"

# The public domain name of the website.
domain="$4"

# Custom argument passed with the code update UI.
# Custom argument.
custom_argument="$5"

# The websites' document root can be derived from the site/env:
docroot="/var/www/html/$site.$env/docroot"

# Acquia recommends the following two practices:
# 1. Hardcode the drush version.
# 2. When running drush, provide the application + url, rather than relying
#    on aliases. This can prevent some hard to trace problems.
DRUSH_CMD="/var/www/html/$site.$env/vendor/bin/drush --verbose --root=$docroot --uri=https://$domain"

# Run profile conversion script if custom argument is "drupal11upgrade".
if [ "$custom_argument" = "drupal11upgrade" ]; then
  $DRUSH_CMD scr $docroot/profiles/contrib/ecms_profile/scripts/drush_profile_convert.php --yes >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/drush-profile-convert-${domain}-$(date +"%Y-%m-%d").log
fi

# Install the asset_injector module if the argument is release301.
# Required as the ecms_profile update hook 11208 is throwing a php error
# during the module installation that prevents recipes from applying.
if [ "$custom_argument" = "release301" ]; then
  $DRUSH_CMD cache-rebuild >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/drush-cache.log
  $DRUSH_CMD pm:install asset_injector --yes >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/asset-injector-install-${domain}-$(date +"%Y-%m-%d").log
fi

# Run `drush updatedb`.
$DRUSH_CMD updatedb --yes >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/drush-update-${domain}-$(date +"%Y-%m-%d").log

# Run features import. Run on individual features so we can identify issues easily.
# Several features are optional and only enabled on the sites that need them
# (search, migrations, and the site-specific content types). Importing a feature
# whose module is not enabled fails, so collect the enabled modules up front and
# only import the features that are actually in use on this site.
log_dir="/var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)"
log_date=$(date +"%Y-%m-%d")
enabled_modules=$($DRUSH_CMD pm:list --type=module --status=enabled --field=name 2>/dev/null)

import_feature() {
  feature="$1"
  log="$log_dir/drush-features-$feature-$domain-$log_date.log"

  # If the module list could not be read, fall back to importing the feature so
  # a failed lookup does not silently skip every import.
  if [ -n "$enabled_modules" ] && ! echo "$enabled_modules" | grep -qx "$feature"; then
    echo "Skipping $feature: the feature is not enabled on $domain." >> "$log"
    return
  fi

  $DRUSH_CMD features:import "$feature" --yes >> "$log"
}

import_feature ecms_basic_page
import_feature ecms_event
import_feature ecms_hotel
import_feature ecms_landing_page
import_feature ecms_location
import_feature ecms_notification
import_feature ecms_paragraphs
import_feature ecms_person
import_feature ecms_press_release
import_feature ecms_promotions
import_feature ecms_publications
import_feature ecms_solr_search
import_feature ecms_emergency_notification
import_feature ecms_executive_orders
import_feature ecms_speeches
import_feature ecms_vaccination_site
import_feature ecms_projects
import_feature ecms_migration_file
import_feature ecms_database_search

# Send email about features status.
$DRUSH_CMD features-list --bundle=ecms | mail -s "Features deploy status for ${domain}" bhamelin@oomphinc.com

# Rebuild caches after features import.
$DRUSH_CMD cache-rebuild >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/drush-cache.log

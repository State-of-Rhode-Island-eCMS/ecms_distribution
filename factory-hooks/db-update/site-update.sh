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

# Run `drush updatedb`.
$DRUSH_CMD updatedb --yes >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/drush-update-${domain}-$(date +"%Y-%m-%d").log

# Run features import. Run on individual features so we can identify issues easily.
# $DRUSH_CMD features:import:all --bundle=ecms --yes >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/drush-features-${domain}-$(date +"%Y-%m-%d").log
$DRUSH_CMD features:import ecms_basic_page --yes >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/drush-features-ecms_basic_page-${domain}-$(date +"%Y-%m-%d").log
$DRUSH_CMD features:import ecms_event --yes >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/drush-features-ecms_event-${domain}-$(date +"%Y-%m-%d").log
$DRUSH_CMD features:import ecms_hotel --yes >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/drush-features-ecms_hotel-${domain}-$(date +"%Y-%m-%d").log
$DRUSH_CMD features:import ecms_landing_page --yes >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/drush-features-ecms_landing_page-${domain}-$(date +"%Y-%m-%d").log
$DRUSH_CMD features:import ecms_location --yes >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/drush-features-ecms_location-${domain}-$(date +"%Y-%m-%d").log
$DRUSH_CMD features:import ecms_notification --yes >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/drush-features-ecms_notification-${domain}-$(date +"%Y-%m-%d").log
$DRUSH_CMD features:import ecms_paragraphs --yes >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/drush-features-ecms_paragraphs-${domain}-$(date +"%Y-%m-%d").log
$DRUSH_CMD features:import ecms_person --yes >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/drush-features-ecms_person-${domain}-$(date +"%Y-%m-%d").log
$DRUSH_CMD features:import ecms_press_release --yes >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/drush-features-ecms_press_release-${domain}-$(date +"%Y-%m-%d").log
$DRUSH_CMD features:import ecms_promotions --yes >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/drush-features-ecms_promotions-${domain}-$(date +"%Y-%m-%d").log
$DRUSH_CMD features:import ecms_publications --yes >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/drush-features-ecms_publications-${domain}-$(date +"%Y-%m-%d").log
$DRUSH_CMD features:import ecms_solr_search --yes >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/drush-features-ecms_solr_search-${domain}-$(date +"%Y-%m-%d").log
$DRUSH_CMD features:import ecms_emergency_notification --yes >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/drush-features-ecms_emergency_notification-${domain}-$(date +"%Y-%m-%d").log

# Send email about features status.
$DRUSH_CMD features-list --bundle=ecms | mail -s "Features deploy status for ${domain}" bhamelin@oomphinc.com

# Rebuild caches after features import.
$DRUSH_CMD cache-rebuild >> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/drush-cache.log

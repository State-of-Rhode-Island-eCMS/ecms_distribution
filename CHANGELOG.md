# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog][] and this project adheres to a
modified Semantic Versioning scheme. See the "Versioning scheme" section of the
[CONTRIBUTING][] file for more information.

[Keep a Changelog]: http://keepachangelog.com/
[CONTRIBUTING]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/docs/release-workflow.md#versioning-scheme

## [Unreleased]
### Added

### Changed

### Deprecated

### Removed

### Fixed

### Security

## [1.10.10] - 2023-11-30
### Changed
- RIGA-6: Update rhodeislandecms/ecms_profile => 0.10.9.
- RIGA-433: Upgrade drupal/content_moderation_notifications 3.5.0 => 3.6.0.
- RIGA-434: Turn off advagg module by default.

### Security
- RIGA-433: Content Moderation Notifications - Moderately critical - Information disclosure - SA-CONTRIB-2023-047.

## [1.10.9] - 2023-11-02
### Added
- RIGA-401: Add google_translator patch 3387636 to resolve HTML render error.

## [1.10.8] - 2023-10-19
### Changed
- RIGA-6: Update rhodeislandecms/ecms_profile => 0.10.7.

## [1.10.7] - 2023-10-19
### Changed
- RIGA-6: Update rhodeislandecms/ecms_profile => 0.10.6.

## [1.10.6] - 2023-10-19
### Added
- RIGA-322: Install drupal/upgrade_status 4.0.0.
- RIGA-401: Install drupal/crop 2.3.0.
- RIGA-401: Install drupal/entity_usage 2.0.0-beta12.
- RIGA-401: Install drupal/focal_point 2.0.2.
- RIGA-401: Install drupal/iek 1.3.0.
- RIGA-415: Install drupal/advagg 6.0.0-alpha1.
- RIGA-415: Install drupal/conditional_fields 4.0.0-alpha5.
- RIGA-415: Install drupal/field_group 3.4.0.
- RIGA-415: Install drupal/quick_node_clone 1.16.0.
- RIGA-322: Add drupal/ckeditor_entity_link patch issue 3296754 comment 3.
- RIGA-322: Add drupal/easy_breadcrumb patch issue 3284123 comment 13.
- RIGA-322: Add drupal/migrate_process_trim patch issue 3288638 comment 2.
- RIGA-322: Install drupal/classy 1.0.2 as contrib module.

### Changed
- RIGA-6: Update rhodeislandecms/ecms_profile => 0.10.5.
- RIGA-6: Update state-of-rhode-island-ecms/ecms_patternlab => 0.7.4.
- RIGA-322: Upgrade drupal/better_exposed_filters 5.0.0 => 6.0.2.
- RIGA-322: Upgrade drupal/captcha 1.10.0 => 2.0.5.
- RIGA-322: Upgrade drupal/components 2.4.0 => 3.0.0-beta3.
- RIGA-322: Upgrade drupal/config_update 1.7.0 => 2.0.0-alpha3.
- RIGA-322: Upgrade drupal/content_moderation_notifications dev-3.x => 3.5.0.
- RIGA-322: Upgrade drupal/ctools 3.11.0 => 3.14.0.
- RIGA-322: Upgrade drupal/devel 4.0.1 => 5.1.2.
- RIGA-322: Upgrade drupal/features 3.12.0 => 3.13.0.
- RIGA-322: Upgrade drupal/feeds 3.0.0-beta2 => 3.0.0-beta4.
- RIGA-322: Upgrade drupal/google_tag 1.4.0 => 2.0.2.
- RIGA-322: Upgrade drupal/google_translator 1.0.0 => 2.1.0.
- RIGA-322: Upgrade drupal/http_cache_control 2.0.0 => 2.1.0.
- RIGA-322: Upgrade drupal/jquery_ui_datepicker 1.4.0 => 2.0.0.
- RIGA-322: Upgrade drupal/jquery_ui_draggable 1.2.0 => 2.0.0.
- RIGA-322: Upgrade drupal/jquery_ui_droppable 1.2.0 => 1.5.0.
- RIGA-322: Upgrade drupal/jquery_ui_slider 1.1.0 => 2.0.0.
- RIGA-322: Upgrade drupal/language_cookie dev-1.x => 2.0.1.
- RIGA-322: Upgrade drupal/language_neutral_aliases 3.0.0 => 3.1.0.
- RIGA-322: Upgrade drupal/layout_builder_modal 1.1.0 => 1.2.0.
- RIGA-322: Upgrade drupal/media_library_theme_reset 1.1.0 => 1.5.0.
- RIGA-322: Upgrade drupal/menu_block dev-1.x => 1.10.0.
- RIGA-322: Upgrade drupal/menu_force 1.2.0 => 2.0.0.
- RIGA-322: Upgrade drupal/migrate_plus 5.3.0 => 6.0.1.
- RIGA-322: Upgrade drupal/migrate_tools 5.1.0 => 6.0.2.
- RIGA-322: Upgrade drupal/page_manager 4.0.0-beta6 => 4.0.0-rc2.
- RIGA-322: Upgrade drupal/panels 4.6.0 => 4.7.0.
- RIGA-322: Upgrade drupal/paragraphs 1.15.0 => 1.16.0.
- RIGA-322: Upgrade drupal/publishcontent 1.5.0 => 1.6.0.
- RIGA-322: Upgrade drupal/rabbit_hole 1.0.0-beta10 => 1.0.0-beta11.
- RIGA-322: Upgrade drupal/search_api_exclude 2.0.0 => 2.0.2.
- RIGA-322: Upgrade drupal/simple_menu_permissions 1.4.0 => 2.0.0.
- RIGA-322: Upgrade drupal/svg_image 1.16.0 => 3.0.1.
- RIGA-322: Upgrade drupal/twig_tweak 2.10.0 => 3.2.1.
- RIGA-322: Upgrade drupal/twig_vardumper 3.0.2 => 3.1.0.
- RIGA-322: Upgrade drupal/webform 6.1.4 => 6.2.0-beta6.
- RIGA-322: Upgrade drupal/webform_encrypt dev-1.x => 2.0.0-alpha1.
- RIGA-322: Upgrade drupal/views_database_connector 1.4.0 => 2.0.1.
- RIGA-322: Upgrade drupal/core-* 9.5.11 => 10.1.5.
- RIGA-322: Upgrade drupal/dynamic_entity_reference 1.16.0 => 3.1.0.
- RIGA-322: Upgrade drupal/geocoder 3.34.0 => 4.9.0.
- RIGA-322: Upgrade drush/drush 10.6.2 => 12.2.0.
- RIGA-322: Update line numbers for '.htacess.patch'.

### Removed
- RIGA-322: Remove module drupal/views_ajax_get.

## [1.10.5] - 2023-09-28
### Changed
- RIGA-414: Update php 8.0 => 8.1 in lando and composer.
- RIGA-414: Upgrade drush/drush 10.3.6 => 10.6.2.
- RIGA-414: Upgrade drush8 => drush10 in Acquia hooks.
- RIGA-414: Update lando version 3.4.0 => 3.18.0 in oomph actions.

## [1.10.4] - 2023-09-21
### Changed
- RIGA-420: Upgrade drupal/core (and dependencies) 9.5.9 => 9.5.11.

### Security
- RIGA-420: Drupal core - Critical - Cache poisoning - SA-CORE-2023-006.

## [1.10.3] - 2023-08-31
### Changed
- RIGA-409: Update drupal/acquia_search 3.1.7 => 3.1.10.
- RIGA-409: Update drupal/acquia_connector 4.0.1 => 4.0.5.
- RIGA-409: Update drupal/acsf 2.73.0 => 2.75.0.

## [1.10.2] - 2023-08-10
### Changed
- RIGA-6: Update rhodeislandecms/ecms_profile => 0.10.2.
- RIGA-406: Update drupal/admin_toolbar 3.4.0 => 3.4.1.
- RIGA-406: Update drupal/easy_breadcrumb ^2.0 => 2.0.5.
- RIGA-406: Update drupal/metatag ^1.14 => ^1.26.
- RIGA-406: Update drupal/token ^1.7 => ^1.12.

## [1.10.1] - 2023-07-27
### Added
- RIGA-377: Add drupal/paragraphs patch issue 3095959 comment 5.
- RIGA-405: Add drupal/core patch issue 3277784 comment 2.

### Changed
- RIGA-6: Update rhodeislandecms/ecms_profile => 0.10.1.

### Fixed
- RIGA-377: Fix permissions to view unpublished paragraphs (see patch).
- RIGA-405: Fix missing sidebar nav menus (core patch).

## [1.10.0] - 2023-07-13
### Changed
- RIGA-6: Upgrade to 1.10.0 to reflect drupal/core 9.4.x => 9.5.x upgrade.
- RIGA-6: Update rhodeislandecms/ecms_profile 0.9.31 => 0.10.0.

## [1.9.9] - 2023-07-13
### Changed
- RIGA-6: Update rhodeislandecms/ecms_profile => 0.9.31.
- RIGA-399: Upgrade drupal/core (and dependencies) 9.4.14 => 9.5.9.
- RIGA-403: Upgrade drupal/address 1.11.0 => 1.12.0.
- RIGA-403: Upgrade drupal/entity_print 2.9.0 => 2.13.0.
- RIGA-403: Upgrade drupal/geocoder 3.31.0 => 3.34.0.
- RIGA-403: Upgrade drupal/geofield 1.52.0 => 1.53.0.
- RIGA-403: Upgrade drupal/media_entity_download 2.1.0 => 2.2.0.
- RIGA-403: Upgrade drupal/smart_date 4.0.0-alpha3 => 4.0.2.

## [1.9.8] - 2023-06-29
### Changed
- RIGA-6: Update rhodeislandecms/ecms_profile => 0.9.30.

## [1.9.7] - 2023-06-22
### Changed
- RIGA-397: Upgrade drupal/office_hours 1.7.0 => 1.11.0.

## [1.9.6] - 2023-06-15
### Added
- RIGA-388: Add patch for drupal/feeds issue 3158678 for uncaught exceptions.
- RIGA-388: Add factory hook to set temp file paths depending on environment.

### Changed
- RIGA-390: Update rhodeislandecms/ecms_profile => 0.9.29.
- RIGA-390: Upgrade drupal/asset_injector 2.16.0 => 2.17.0.
- RIGA-390: Upgrade drupal/encrypt 3.0.0 => 3.1.0.
- RIGA-390: Upgrade drupal/key 1.15.0 => 1.17.0.
- RIGA-390: Upgrade drupal/metatag 1.21.0 => 1.25.0.
- RIGA-390: Upgrade drupal/real_aes 2.4.0 => 2.5.0.
- RIGA-390: Upgrade drupal/robotstxt 1.4.0 => 1.5.0.
- RIGA-390: Upgrade drupal/simple_sitemap 3.11.0 => 4.1.6.

## [1.9.5] - 2023-05-25
### Changed
- RIGA-387: Upgrade drupal/admin_toolbar 3.3.0 => 3.4.0.
- RIGA-387: Upgrade drupal/bigmenu 2.0.0-rc2 => 2.0.0-rc3.
- RIGA-387: Upgrade drupal/extlink 1.6.0 => 1.7.0.
- RIGA-387: Update rhodeislandecms/ecms_profile => 0.9.28.

## [1.9.4] - 2023-05-11
### Changed
- RIGA-6: Update rhodeislandecms/ecms_profile => 0.9.27.
- RIGA-382: Upgrade drupal/captcha 1.9.0 => 1.10.0.
- RIGA-382: Upgrade drupal/webform 6.1.3 => 6.1.4.
- RIGA-382: Upgrade drupal/webform_views 5.0.0 => 5.1.0.

## [1.9.3] - 2023-04-27
### Added
- RIGA-379: Install nnnick/chartjs v3.9.1 locally to avoid CDN warning.
- RIGA-380: Install drupal/feeds_ex 1.0.0-beta3.

### Changed
- RIGA-379: Upgrade drupal/core 9.4.12 => 9.4.14.

### Security
- RIGA-379: Drupal core - Moderately critical - Access bypass - SA-CORE-2023-005.

## [1.9.2] - 2023-04-20
### Added
- RIGA-372: Install drupal/autologout 1.4.0.
- RIGA-372: Add drupal/core patch, issue 3301239, comment 7.

### Changed
- RIGA-6: Update rhodeislandecms/ecms_profile => 0.9.25.
- RIGA-371: Update user role permissions.
- RIGA-373: Upgrade drupal/acquia_connector 4.0.1 => 4.0.4.
- RIGA-373: Upgrade drupal/acquia_search 3.1.4 => 3.1.7.
- RIGA-373: Upgrade drupal/search_api 1.28.0 => 1.29.0.
- RIGA-373: Upgrade drupal/search_api_solr 4.2.9 => 4.2.10.

## [1.9.1] - 2023-04-06
### Changed
- RIGA-6: Update rhodeislandecms/ecms_profile => 0.9.24.
- RIGA-6: Update state-of-rhode-island-ecms/ecms_patternlab => 0.7.3.

## [1.9.0] - 2023-03-16
### Changed
- RIGA-369: Upgrade drupal/core 9.4.10 => 9.4.12.

### Security
- RIGA-369: Drupal core - Moderately critical - Information Disclosure - SA-CORE-2023-002.
- RIGA-369: Drupal core - Moderately critical - Information Disclosure - SA-CORE-2023-003.
- RIGA-369: Drupal core - Moderately critical - Access bypass - SA-CORE-2023-004.

## [1.8.9] - 2023-03-09
### Added
- RIGA-363: Install drupal/ckeditor 1.0.1.
- RIGA-363: Install drupal/ckeditor_entity_link 1.3.0.

### Changed
- RIGA-6: Update ecms_profile => 0.9.23.
- RIGA-364: Upgrade drupal/smart_date 3.6.1 => 4.0.0-alpha3.
- RIGA-364: Upgrade drupal/jquery_ui_datepicker 1.2.0 => 1.4.0.
- RIGA-364: Upgrade drupal/jquery_ui_touch_punch 1.0.0 => 1.1.0.
- RIGA-364: Upgrade drupal/jsonapi_extras 3.21.0 => 3.23.0.
- RIGA-364: Upgrade drupal/address 1.10.0 => 1.11.0.

## [1.8.8] - 2023-02-09
### Changed
- RIGA-349: Upgrade drupal/acquia_search 3.0.9 => 3.1.4.
- RIGA-355: Upgrade drupal/media_library_form_element 2.0.4 => 2.0.6.
- RIGA-357: Upgrade drupal/captcha 1.2.0 => 1.9.0.
- RIGA-357: Upgrade drupal/geocoder 3.29.0 => 3.31.0.
- RIGA-357: Upgrade drupal/geofield 1.20.0 => 1.52.0.
- RIGA-357: Upgrade drupal/geolocation 3.2.0 => 3.12.0.

### Security
- RIGA-355: Media Library Form API Element => 2.0.6 (SA-CONTRIB-2023-004).

## [1.8.7] - 2023-01-25
### Added
- RIGA-354: Install drupal/menu_force 1.2.0.
- RIGA-346: Add drupal/menu_admin_per_menu 1.5.0 to composer.lock.
- RIGA-346: Add drupal/simple_menu_permissions 1.4.0 to composer.lock.

### Changed
- RIGA-346: Update ecms_profile to 0.9.22.

### Security
- RIGA-355: Upgrade drupal/core 9.4.8 => 9.4.10 (SA-CORE-2023-001).

## [1.8.6] - 2023-01-12
### Changed
- RIGA-349: Upgrade drupal/asset_injector 2.12.0 => 2.16.0.
- RIGA-349: Upgrade drupal/auto_entitylabel 3.0.0-beta4 => 3.0.0.
- RIGA-349: Upgrade drupal/entity_print 2.6.0 => 2.9.0.
- RIGA-349: Upgrade drupal/scheduled_transitions 2.2.1 => 2.3.0.

## [1.8.5] - 2022-12-15
### Changed
- RIGA-345: Upgrade drupal/admin_toolbar 3.2.1 => 3.3.0.
- RIGA-345: Upgrade drupal/simple_oauth 5.2.0 => 5.2.3.
- RIGA-345: Upgrade drupal/smart_date 3.5.1 => 3.6.1.

## [1.8.4] - 2022-12-08
### Changed
- RIGA-333: Upgrading drupal/entity_reference_revisions 1.9.0 => 1.10.0.
- RIGA-6: Updated ecms_profile to 0.9.19.

## [1.8.3] - 2022-11-17
### Changed
- RIGA-6: Updated ecms_profile to 0.9.18.
- RIGA-358: Upgrading drupal/search_api (1.27.0 => 1.28.0).
- RIGA-358: Upgrading drupal/search_api_solr (4.2.8 => 4.2.9).
- RIGA-358: Upgrading drupal/acquia_search (3.0.7 => 3.0.9).
- RIGA-358: Upgrading drupal/asset_injector (2.10.0 => 2.12.0).
- RIGA-358: Upgrading drupal/twig_tweak (2.9.0 => 2.10.0).
- RIGA-358: Upgrading drupal/webform_views (5.0-beta1 => 5.0.0).
- RIGA-329: Upgrading drupal/acquia_connector (3.0.0 => 4.0.0).

## [1.8.2] - 2022-10-27
### Security
- RIGA-325: Search API (1.26.0 => 1.27.0) - Moderately critical - Information Disclosure - SA-CONTRIB-2022-059.

## [1.8.1] - 2022-10-20
### Changed
- RIGA-6: Updated ecms_patternlab to 0.7.2.
- RIGA-6: Updated ecms_profile to 0.9.16.

## [1.8.0] - 2022-10-20
### Changed
- RIGA-312: Upgrading drupal/admin_toolbar (3.1.1 => 3.2.1).
- RIGA-312: Upgrading drupal/file_delete (1.0.0 => 2.0.0).
- RIGA-315: Update drupal/core 9.4.7 => 9.4.8.
- RIGA-312: Upgrading drupal/acquia_connector (3.0.5 => 3.0.6).
- RIGA-312: Upgrading drupal/acquia_search (3.0.5 => 3.0.7).
- RIGA-312: Upgrading drupal/search_api (1.25.0 => 1.26.0).
- RIGA-307: Upgrading drupal/google_translate (1.0.0-rc1 => 1.0.0).

## [1.7.9] - 2022-10-06
### Changed
- RIGA-311: Upgrade drupal/acquia_purge 1.2.0 => 1.3.0.
- RIGA-311: Upgrade drupal/admin_toolbar 3.1.0 => 3.1.1.
- RIGA-311: Upgrade drupal/consumers 1.12.0 => 1.13.0.
- RIGA-311: Upgrade drupal/feeds 3.0.0-beta1 => 3.0.0-beta2.
- RIGA-311: Upgrade drupal/moderated_content_bulk_publish 2.0.11 => 2.0.19.
- RIGA-311: Upgrade drupal/office_hours 1.6.0 => 1.7.0.
- RIGA-311: Upgrade drupal/purge 3.3.0 => 3.4.0.
- RIGA-298: Added local patch for 3 outstanding webform_encrypt issues.
- RIGA-6: Updated ecms_profile to 0.9.15.

## [1.7.8] - 2022-09-29
### Security
- RIGA-313: Upgrade drupal/core 9.4.5 => 9.4.7 (with all dependencies).
- RIGA-313: Drupal core - Critical - Multiple vulnerabilities - SA-CORE-2022-016.

## [1.7.7] - 2022-09-22
### Changed
- RIGA-286: Updates to support PHP 8.0.
- RIGA-6: Updated ecms_profile to 0.9.14.

## [1.7.6] - 2022-09-15
### Changed
- RIGA-308: Upgrade drupal/acquia_connector 3.0.4 => 3.0.5.
- RIGA-308: Upgrade drupal/acsf 2.72 => 2.73.
- RIGA-308: Upgrade drupal/memcache 2.4.0 => 2.5.0.
- RIGA-308: Upgrade drupal/metatag 1.19.0 => 1.21.0.
- RIGA-308: Upgrade drupal/pathauto 1.10.0 => 1.11.0.
- RIGA-308: Upgrade drupal/purge 3.2.0 => 3.3.0.
- RIGA-308: Upgrade drupal/redirect 1.7.0 => 1.8.0.
- RIGA-6: Updated ecms_profile to 0.9.13.

## [1.7.5] - 2022-08-11
### Changed
- RIGA-282: Update drupal/core 9.3.19 => 9.4.5.
- RIGA-282: Update core patch for issue 1356276.
- RIGA-282: Update line numbers of htaccess patch.
- RIGA-297: Upgrade drupal/acsf_duplication 2.68.0 => 2.72.0.
- RIGA-297: Upgrade drupal/acsf_theme 2.68.0 => 2.72.0.
- RIGA-297: Upgrade drupal/acsf_variables 2.68.0 => 2.72.0.
- RIGA-297: Upgrade drupal/memcache 2.3.0 => 2.4.0.
- RIGA-297: Upgrade drupal/search_api 1.23.0 => 1.25.0.
- RIGA-297: Upgrade drupal/search_api_solr 4.2.7 => 4.2.8.
- RIGA-297: Upgrade drupal/token 1.10.0 => 1.11.0.
- RIGA-297: Upgrade drupal/jsonapi_extras 3.20.0 => 3.21.0.
- RIGA-6: Updated ecms_profile to 0.9.12.

## [1.7.4] - 2022-07-28
### Changed
- RIGA-285: Update Paragraphs to 1.14.
- RIGA-6: Updated ecms_profile to 0.9.11.

### Security
- RIGA-293: Updated Drupal core to 9.3.19.
- RIGA-293: Drupal core - Moderately critical - Information Disclosure - SA-CORE-2022-012.
- RIGA-293: Drupal core - Moderately critical - Access Bypass - SA-CORE-2022-013.
- RIGA-293: Drupal core - Critical - Arbitrary PHP code execution - SA-CORE-2022-014.
- RIGA-293: Drupal core - Moderately critical - Multiple vulnerabilities - SA-CORE-2022-015.
- RIGA-285: Update Entity Print to 2.6 (SA-CONTRIB-2022-048).

## [1.7.3] - 2022-07-14
### Changed
- RIGA-285: Updated CTools to 3.8.
- RIGA-285: Updated Key to 1.15.
- RIGA-285: Updated Migrate Plus to 5.3.
- RIGA-285: Updated Migrate Tools to 5.1.
- RIGA-285: Updated Moderation Dashboard to 1.0.0-beta3.
- RIGA-285: Updated Publish Content to 1.5.
- RIGA-285: Updated Rabbit Hole to 1.0.0-beta10.
- RIGA-285: Updated Real AES to 2.4.
- RIGA-6: Updated ecms_profile to 0.9.10.

## [1.7.2] - 2022-06-30
### Changed
- RIGA-268: Updated Consumers to 1.12.
- RIGA-268: Updated Entity Print to 2.5.
- RIGA-268: Updated Feeds to 3.0.0-beta1.
- RIGA-268: Updated JSON:API Extras to 3.20.
- RIGA-268: Updated Media Entity Download to 2.1.
- RIGA-268: Updated Media File Delete to 1.1.1.
- RIGA-268: Updated Metatag to 1.19.
- RIGA-268: Updated Pathauto to 1.10.
- RIGA-6: Updated ecms_profile to 0.9.9.

## [1.7.1] - 2022-06-16
### Changed
- RIGA-268: Updated Address to 1.10.
- RIGA-268: Updated Dynamic Entity Reference to 1.16.
- RIGA-268: Updated Easy Breadcrumb to 2.0.3.
- RIGA-268: Updated Honeypot to 2.1.1.
- RIGA-268: Updated Layout Builder Restrictions to 2.13.
- RIGA-268: Updated Media Library Theme Reset to 1.1.0.
- RIGA-268: Updated moderated_content_bulk_publish to 2.0.11.
- RIGA-268: Updated Office Hours to 1.6.0.
- RIGA-6: Updated ecms_profile to 0.9.8.

### Security
- RIGA-278: Updated Drupal core to 9.3.16 (SA-CORE-2022-011).

## [1.7.0] - 2022-06-09
### Changed
- RIGA-268: Updated Acquia Connector to 3.0.4.
- RIGA-268: Updated Acquia Purge to 1.2.
- RIGA-268: Updated Acquia Search to 3.0.5.
- RIGA-268: Updated Allowed Formats to 1.5.
- RIGA-268: Updated Purge to 3.2.
- RIGA-268: Updated Search API to 1.23.
- RIGA-268: Updated Search API Solr to 4.2.7.
- RIGA-268: Updated Simple XML Sitemap to 3.11.
- RIGA-268: Updated Drupal core to 9.3.15.
- RIGA-6: Updated ecms_profile to 0.9.7.
- RIGA-6: Updated ecms_patternlab to 0.7.1.

## [1.6.9] - 2022-06-02
### Changed
- RIGA-268: Updated Token to 1.10.
- RIGA-268: Updated SVG Image to 1.16.
- RIGA-268: Updated Asset Injector to 1.10.
- RIGA-268: Updated Memcache to 2.3.
- RIGA-6: Updated ecms_profile to 0.9.6.

### Security
- RIGA-271: Updated Drupal core to 9.3.14 (SA-CORE-2022-010).

## [1.6.8] - 2022-05-19
### Changed
- RIGA-233: Updated Drupal core to 9.3.13.
- RIGA-6: Updated ecms_profile to 0.9.5.

## [1.6.7] - 2022-05-12
### Added
- RIGA-262: Post site install site factory hook to set site email.
- RIGA-264: Post site duplication site factory hook to update Solr config.

### Changed
- RIGA-260: Add rewrite rule for no_robots.txt.
- RIGA-6: Updated ecms_profile to 0.9.4.
- RIGA-6: Updated ecms_patternlab to 0.7.0.

## [1.6.6] - 2022-04-28
### Changed
- RIGA-6: Updated ecms_profile to 0.9.2.

### Security
- RIGA-258: Updated Drupal core to 9.3.12 (SA-CORE-2022-009).

## [1.6.5] - 2022-04-19
### Changed
- RIGA-6: Updated ecms_profile to 0.9.1.
- RIGA-6: Updated ecms_patternlab to 0.6.9.

## [1.6.4] - 2022-04-07
### Changed
- RIGA-6: Updated ecms_profile to 0.9.0.

### Removed
- RIGA-244: Removed core robots.txt from the scaffold process.

## [1.6.3] - 2022-03-31
### Changed
- RIGA-6: Updated ecms_profile to 0.8.9.

### Security
- RIGA-233: Updated Drupal core to 9.3.9 (SA-CORE-2022-006).

## [1.6.2] - 2022-03-18
### Changed
- RIGA-6: Updated ecms_profile to 0.8.8.

## [1.6.1] - 2022-03-17
### Changed
- RIGA-6: Updated ecms_profile to 0.8.7 (0.8.6 needed a hotfix for easy breadcrumb config).

### Security
- RIGA-218: Updated Drupal core to 9.3.8 (SA-CORE-2022-005).

## [1.6.0] - 2022-02-17
### Changed
- RIGA-218: Update webform to version 6.1.3.
- RIGA-6: Updated ecms_profile to 0.8.5.

### Security
- RIGA-218: Updated Drupal core to 9.2.13 (SA-CORE-2022-003, -004).

## [1.5.9] - 2022-02-10
### Added
- RIGA-199: Added jQuery UI Touch Punch to libraries.

### Changed
- RIGA-6: Updated ecms_profile to 0.8.4.
- RIGA-6: Updated ecms_patternlab to 0.6.8.

## [1.5.8] - 2022-01-26
### Changed
- RIGA-6: Updated local development documentation.
- RIGA-6: Updated ecms_profile to 0.8.3.

### Security
- RIGA-202: Update core to 9.2.11.

## [1.5.7] - 2022-01-13
### Changed
- RIGA-6: Updated ecms_profile to 0.8.2.
- RIGA-6: Updated ecms_patternlab to 0.6.7.

## [1.5.6] - 2022-01-06
### Changed
- RIGA-6: Updated ecms_profile to 0.8.1.

### Security
- RIGA-189: Updated Simple OAuth to 8.x-4.6 (SA-CONTRIB-2022-002).

## [1.5.5] - 2021-12-09
### Changed
- RIGA-6: Updated ecms_profile to 0.8.0.

### Security
- RIGA-168: Upgrading openid_connect_windows_aad 1.3.0 => 1.4.0 (SA-CONTRIB-2021-044).

## [1.5.4] - 2021-12-02
### Changed
- RIGA-6: Updated ecms_profile to 0.7.9.

## [1.5.3] - 2021-11-18
### Changed
- RIGA-6: Updated ecms_profile to 0.7.8.
- RIGA-6: Updated ecms_patternlab to 0.6.6.

## [1.5.2] - 2021-11-04
### Added
- RIGA-158: Add patch for media revision ui module issue 3247661.

### Changed
- RIGA-160: Add external database connection settings to new factory hook.
- RIGA-159: Updated main rewrite rule to exclude the RIAG website.
- RIGA-6: Updated ecms_profile to 0.7.7.
- RIGA-6: Updated ecms_patternlab to 0.6.5.

## [1.5.1] - 2021-10-21
### Changed
- RIGA-6: Updated ecms_profile to 0.7.6.

## [1.5.0] - 2021-10-14
### Changed
- RIGA-6: Updated ecms_profile to 0.7.5.
-
## [1.4.9] - 2021-09-28
### Changed
- RIGA-6: Updated ecms_profile to 0.7.4.

## [1.4.8] - 2021-09-17
### Changed
- RIGA-6: Updated ecms_patternlab to 0.6.4.

## [1.4.7] - 2021-09-16
### Changed
- RIGA-6: Updated ecms_profile to 0.7.3.
- RIGA-6: Updated ecms_patternlab to 0.6.3.

### Security
- RIGA-135: Updated Drupal core to 9.2.4 (SA-CORE-006 - SA-CORE-010).

## [1.4.6] - 2021-09-09
### Changed
- RIGA-6: Updated ecms_profile to 0.7.2.

## [1.4.5] - 2021-09-01
### Changed
- RIGA-6: Updated ecms_patternlab to 0.6.2.

## [1.4.4] - 2021-09-01
### Changed
- RIGA-6: Updated ecms_profile to 0.7.1.

## [1.4.3] - 2021-08-26
### Changed
- RIGA-106: Updated jsonapi_extras to 3.19.
- RIGA-6: Updated ecms_profile to 0.7.0.
- RIGA-6: Updated ecms_patternlab to 0.6.1.

## [1.4.2] - 2021-08-19
### Added
- RIGA-99: Add features email report to site update script.

### Changed
- RIGA-6: Updated ecms_profile to 0.6.9.

### Security
- RIGA-110: Updated Drupal core to 9.2.4 (SA-CORE-005).

## [1.4.1] - 2021-08-12
### Changed
- RIGA-6: Updated ecms_profile to 0.6.8.
- RIGA-6: Updated ecms_patternlab to 0.6.0.

## [1.4.0] - 2021-08-05
## [1.3.7] - 2021-08-05
### Changed
- RIGA-96: Updated Drupal core to 9.2.2.
- RIGA-98: Updated search module dependencies.
- RIGA-101: Updated scheduled_transitions module to 2.1.
- RIGA-6: Updated ecms_profile to 0.6.7.

## [1.3.6] - 2021-07-15
### Changed
- RIGA-6: Updated ecms_profile to 0.6.5.
- RIGA-6: Updated ecms_patternlab to 0.5.9.

## [1.3.5] - 2021-06-30
### Changed
- RIGA-6: Updated ecms_profile to 0.6.4.

## [1.3.4] - 2021-06-30
### Changed
- RIGA-6: Updated ecms_patternlab to 0.5.8.
- RIGA-6: Updated ecms_profile to 0.6.3.

## [1.3.3] - 2021-06-23
### Added
- RIGA-18: Added patch to webform encrypt to support global encryption for a given form.

### Changed
- RIGA-92: Update ACSF module including automatic updates to site factory hooks.
- RIGA-90: Updated auto_entitylabel, components, dynamic_entity_reference, easy_breadcrumb.
- RIGA-90: Updated entity_reference_revisions, extlink, features, layout_builder_restrictions.
- RIGA-90: Updated simple_sitemap, twig_tweak.
- RIGA-92: Update webform module to 6.0.3.

### Security
- RIGA-6: Updated Chaos Tool Suite to 3.7 (SA-CONTRIB-2021-015).

## [1.3.2] - 2021-06-03
### Changed
- RIGA-6: Updated ecms_profile to 0.6.1.

### Security
- RIGA-80: Updated Chaos Tool Suite to 3.6 (SA-CONTRIB-2021-009).
- RIGA-87: Updated core to 9.1.9 (SA-CORE-2021-003).

## [1.3.1] - 2021-05-21
### Changed
- RIGA-6: Updated ecms_profile to 0.6.0.

## [1.3.0] - 2021-05-21
### Changed
- RIGA-6: Updated ecms_patternlab to 0.5.6.
- RIGA-6: Updated ecms_profile to 0.5.9.
- RIGA-24: Update Drupal core from 9.0.12 to 9.1.8.

## [1.2.4] - 2021-04-26
### Changed
- RIGA-6: Updated ecms_patternlab to 0.5.5.

## [1.2.3] - 2021-04-22
### Changed
- RIGA-35: Update solr override settings.

## [1.2.2] - 2021-04-22
### Changed
- RIGA-6: Updated ecms_patternlab to 0.5.4.
- RIGA-6: Updated ecms_profile to 0.5.7.

### Security
- RIGA-59: Update core: SA-CORE-2021-002.

## [1.2.1] - 2021-04-09
### Changed
- RIGA-6: Updated ecms_patternlab to 0.5.3.
- RIGA-6: Updated ecms_profile to 0.5.6.

## [1.2.0] - 2021-04-06
### Changed
- RIGA-6: Updated ecms_profile to 0.5.5.

## [1.1.9] - 2021-03-24
### Added
- RIGA-50: Added .htaccess patch to redirect all www. requests.

## [1.1.8] - 2021-03-19
### Changed
- RIGA-6: Updated ecms_patternlab to 0.5.2.
- RIGA-6: Updated ecms_profile to 0.5.4.

## [1.1.7] - 2021-03-10
### Changed
- RIGA-6: Added unique log filenames for drush-update and drush-features.
- RIGA-6: Updated ecms_patternlab to 0.5.1.
- RIGA-6: Updated ecms_profile to 0.5.3.

### Security
- SA-CONTRIB-2021-004: Webform - Moderately critical - Access bypass.

## [1.1.6] - 2021-03-04
- RIG-6: Updated ecms_profile to 0.5.2.

## [1.1.5] - 2021-03-04
### Changed
- RIG-6: Added --yes flag to updatedb command.
- RIG-6: Added conditions to disable memcache during site installs.
- RIG-6: Updated ecms_patternlab to 0.5.0.
- RIG-6: Updated ecms_profile to 0.5.1.

## [1.1.4] - 2021-02-18
### Changed
- RIG-6: Updated ecms_profile to 0.5.0.

## [1.1.3] - 2021-02-17
### Changed
- RIG-6: Updated ecms_patternlab to 0.4.9.
- RIG-6: Updated ecms_profile to 0.4.9.

## [1.1.2] - 2021-02-17
### Changed
- RIG-6: Updated ecms_patternlab to 0.4.8.
- RIG-6: Updated ecms_profile to 0.4.8.

## [1.1.1] - 2021-02-12
### Added
- RIG-222: Adds Acquia Solr Search settings.

### Changed
- RIG-6: Updated ecms_patternlab to 0.4.7.
- RIG-6: Updated ecms_profile to 0.4.7.

## [1.1.0] - 2021-01-28
### Changed
- RIG-6: Updated ecms_patternlab to 0.4.6.
- RIG-6: Updated ecms_profile to 0.4.6.

### Security
- RIG-243: Updated core to 9.0.11 (SA-CORE-2021-001).

## [1.0.9] - 2021-01-19
### Changed
- RIG-6: Updated ecms_patternlab to 0.4.5.
- RIG-6: Updated ecms_profile to 0.4.5.

## [1.0.8] - 2021-01-19
### Changed
- RIG-6: Updated ecms_patternlab to 0.4.4.
- RIG-6: Updated ecms_profile to 0.4.4.

## [1.0.7] - 2021-01-15
### Changed
- RIG-6: Updated ecms_patternlab to 0.4.3.
- RIG-6: Updated ecms_profile to 0.4.3.

## [1.0.6] - 2021-01-11
### Changed
- RIG-6: Updated ecms_patternlab to 0.4.2.
- RIG-6: Updated ecms_profile to 0.4.2.

## [1.0.5] - 2021-01-06
### Changed
- RIG-6: Updated ecms_profile to 0.4.1.

## [1.0.4] - 2021-01-05
### Changed
- RIG-6: Updated ecms_patternlab to 0.4.1.
- RIG-6: Updated ecms_profile to 0.4.0.

## [1.0.3] - 2020-12-22
### Changed
- RIG-6: Updated ecms_patternlab to 0.4.0.

## [1.0.2] - 2020-12-18
### Changed
- RIG-6: Updated ecms_profile to 0.3.9.

## [1.0.1] - 2020-12-16
### Changed
- RIG-6: Updated ecms_profile to 0.3.8.

## [1.0.0] - 2020-12-16
### Changed
- RIG-6: Updated ecms_patternlab to 0.3.9.
- RIG-6: Updated ecms_profile to 0.3.7.

## [0.3.9] - 2020-12-15
### Changed
- RIG-6: Updated ecms_profile to 0.3.6.

## [0.3.8] - 2020-12-15
### Changed
- RIG-6: Updated ecms_patternlab to 0.3.8.
- RIG-6: Updated ecms_profile to 0.3.5.

## [0.3.7] - 2020-12-14
### Added
- RIG-171: Add Acquia memcache PHP script to post-settings factory hooks.

### Changed
- RIG-6: Updated ecms_patternlab to 0.3.7.
- RIG-6: Updated ecms_profile to 0.3.4.

## [0.3.6] - 2020-12-14
### Changed
- RIG-6: Updated ecms_patternlab to 0.3.6.

## [0.3.5] - 2020-12-14
### Changed
- RIG-6: Updated ecms_patternlab to 0.3.5.
- RIG-6: Updated ecms_profile to 0.3.3.

### Fixed
- RIG-185: Added the webform library dependencies.

## [0.3.4] - 2020-12-12
### Changed
- RIG-6: Updated ecms_profile to 0.3.2.

## [0.3.3] - 2020-12-11
### Changed
- RIG-6: Updated ecms_patternlab to 0.3.4.
- RIG-6: Updated ecms_profile to 0.3.1.

## [0.3.2] - 2020-12-10
### Changed
- RIG-6: Updated ecms_patternlab to 0.3.2.
- RIG-6: Updated ecms_profile to 0.3.0.

## [0.3.1] - 2020-12-08
### Changed
- RIG-6: Added Drupal recommended .htaccess to config/default/ folder.
- RIG-6: Updated ecms_patternlab to 0.3.1.
- RIG-6: Updated ecms_profile to 0.2.9.

### Fixed
- RIG-165: Unable to install a new site.

## [0.3.0] - 2020-12-04
### Changed
- RIG-6: Updated ecms_patternlab to 0.3.0.
- RIG-6: Updated ecms_profile to 0.2.8.

## [0.2.9] - 2020-12-03
### Fixed
- Fixed deployment error due to https redirect and CLI conflict.

## [0.2.8] - 2020-12-03
### Added
- RIG-158: Force HTTPS on production environment.
### Changed
- RIG-6: Updated ecms_patternlab to 0.2.9.
- RIG-6: Updated ecms_profile to 0.2.7.

## [0.2.7] - 2020-12-01
### Changed
- RIG-6: Updated ecms_patternlab to 0.2.8.
- RIG-6: Updated ecms_profile to 0.2.6.
### Security
- RIG-157: Security Update: SA-CORE-2020-013

## [0.2.6] - 2020-11-25
### Changed
- RIG-6: Updated ecms_patternlab to 0.2.7.
- RIG-6: Updated ecms_profile to 0.2.5.

### Fixed
- RIG-6 - Fixed settings variable and path for config directories.

## [0.2.5] - 2020-11-24
### Changed
- RIG-6: Updated ecms_patternlab to 0.2.6.
- RIG-6: Updated ecms_profile to 0.2.4

## [0.2.4] - 2020-11-20
### Changed
- RIG-6: Updated ecms_patternlab to 0.2.5

## [0.2.3] - 2020-11-19
### Changed
- RIG-6: Updated ecms_profile to 0.2.3
- RIG-6: Updated ecms_patternlab to 0.2.4
### Security
- RIG-152: SA-CORE-2020-012.

## [0.2.2] - 2020-11-18
### Changed
- RIG-6: Updated ecms_patternlab to 0.2.3.
- RIG-6: Updated ecms_profile to 0.2.2.

## [0.2.1] - 2020-11-13
### Changed
- RIG-6: Updated ecms_patternlab to 0.2.2.
- RIG-6: Updated ecms_profile to 0.2.1.

## [0.2.0] - 2020-11-12
### Changed
- RIG-6: Updated ecms_patternlab to 0.2.0 (0.1.10).
- RIG-6: Updated ecms_profile to 0.2.0.

## [0.1.13] - 2020-11-11
### Changed
- RIG-6: Updated ecms_patternlab to 0.1.9.
- RIG-6: Updated ecms_profile to 0.1.9.
- Updated oomphinc/composer-installers-extender to ^2.0.

## [0.1.12] - 2020-11-06
### Changed
- RIG-6: Updated ecms_patternlab to 0.1.8.
- RIG-6: Updated ecms_profile to 0.1.8.

## [0.1.11] - 2020-11-05
### Changed
- RIG-6 - Hotfix pattern lab to 0.1.7.

## [0.1.10] - 2020-11-05
### Added
- RIG-6 - Added config.php post settings hook.

### Changed
- RIG-6: Updated ecms_patternlab to 0.1.6.
- RIG-6: Updated ecms_profile to 0.1.7.

## [0.1.9] - 2020-10-30
### Changed
- RIG-6 - Updated ecms_profile to use 0.1.6.

## [0.1.8] - 2020-10-30
### Changed
- RIG-6 - Updated ecms_patternlab to use 0.1.5.

## [0.1.7] - 2020-10-30
### Changed
- RIG-6 - Updated ecms_patternlab to use 0.1.4.
- RIG-6 - Updated the ecms_profile to use 0.1.5.
- RIG-6 - Locked Lando's composer version to 1.10.1.

## [0.1.6] - 2020-10-28
### Changed
- RIG-6 - Updated ecms_patternlab to use 0.1.3.
- RIG-6 - Updated ecms_profile to use 0.1.4.

## [0.1.5] - 2020-10-27
### Changed
- RIG-6: Updated ecms_patternlab to use 0.1.2.
- RIG-6: Updated ecms_profile to use 0.1.3.

### Fixed
- RIG-122: Fixed the features import drush command.

## [0.1.4] - 2020-10-22
### Changed
- Changed the ecms_patternlab to use 0.1.1.
- Changed the ecms_profile to use 0.1.2.

## [0.1.3] - 2020-10-19
### Added
- Added verbose logging to the site factory db-update script.

### Changed
- Changed the ecms_profile to use 0.1.1.
- Changed the ecms_patternlab to use 0.1.0.

## [0.1.2] - 2020-10-15
### Added
- RIG-12: Added the secrets.php post settings hook.
- RIG-28: Added the automated ci/cid workflow to push to Acquia.
- RIG-37: Added custom package repository for ECMS Pattern Lab integration.
- RIG-83: Updated the Pattern lab requirement and all dependencies.
- RIG-96: Added db-update cloud hook to revert features.

### Changed
- RIG-67: Updated composer lock file to ensure latest dependencies are installed.
- RIG-6: Updated the ecms_profile to the initial 0.1.0 release.

### Fixed
- RIG-28: Updated the lock file to include ecms_profile requirements.

## [0.1.1] - 2020-10-09
## [0.1.0] - 2020-10-07
### Added
- Initial Release of the site.

[Unreleased]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.10.10...HEAD
[1.10.10]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.10.9...1.10.10
[1.10.9]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.10.8...1.10.9
[1.10.8]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.10.7...1.10.8
[1.10.7]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.10.6...1.10.7
[1.10.6]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.10.5...1.10.6
[1.10.5]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.10.4...1.10.5
[1.10.4]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.10.3...1.10.4
[1.10.3]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.10.2...1.10.3
[1.10.2]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.10.1...1.10.2
[1.10.1]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.10.0...1.10.1
[1.10.0]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.9.9...1.10.0
[1.9.9]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.9.8...1.9.9
[1.9.8]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.9.7...1.9.8
[1.9.7]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.9.6...1.9.7
[1.9.6]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.9.5...1.9.6
[1.9.5]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.9.4...1.9.5
[1.9.4]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.9.3...1.9.4
[1.9.3]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.9.2...1.9.3
[1.9.2]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.9.1...1.9.2
[1.9.1]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.9.0...1.9.1
[1.9.0]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.8.9...1.9.0
[1.8.9]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.8.8...1.8.9
[1.8.8]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.8.7...1.8.8
[1.8.7]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.8.6...1.8.7
[1.8.6]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.8.5...1.8.6
[1.8.5]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.8.4...1.8.5
[1.8.4]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.8.3...1.8.4
[1.8.3]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.8.2...1.8.3
[1.8.2]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.8.1...1.8.2
[1.8.1]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.8.0...1.8.1
[1.8.0]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.7.9...1.8.0
[1.7.9]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.7.8...1.7.9
[1.7.8]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.7.7...1.7.8
[1.7.7]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.7.6...1.7.7
[1.7.6]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.7.5...1.7.6
[1.7.5]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.7.4...1.7.5
[1.7.4]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.7.3...1.7.4
[1.7.3]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.7.2...1.7.3
[1.7.2]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.7.1...1.7.2
[1.7.1]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.7.0...1.7.1
[1.7.0]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.6.9...1.7.0
[1.6.9]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.6.8...1.6.9
[1.6.8]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.6.7...1.6.8
[1.6.7]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.6.6...1.6.7
[1.6.6]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.6.5...1.6.6
[1.6.5]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.6.4...1.6.5
[1.6.4]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.6.3...1.6.4
[1.6.3]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.6.2...1.6.3
[1.6.2]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.6.1...1.6.2
[1.6.1]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.6.0...1.6.1
[1.6.0]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.5.9...1.6.0
[1.5.9]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.5.8...1.5.9
[1.5.8]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.5.7...1.5.8
[1.5.7]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.5.6...1.5.7
[1.5.6]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.5.5...1.5.6
[1.5.5]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.5.4...1.5.5
[1.5.4]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.5.3...1.5.4
[1.5.3]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.5.2...1.5.3
[1.5.2]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.5.1...1.5.2
[1.5.1]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.5.0...1.5.1
[1.5.0]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.4.9...1.5.0
[1.4.9]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.4.8...1.4.9
[1.4.8]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.4.7...1.4.8
[1.4.7]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.4.6...1.4.7
[1.4.6]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.4.5...1.4.6
[1.4.5]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.4.4...1.4.5
[1.4.4]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.4.3...1.4.4
[1.4.3]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.4.2...1.4.3
[1.4.2]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.4.1...1.4.2
[1.4.1]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.4.0...1.4.1
[1.4.0]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.3.7...1.4.0
[1.3.7]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.3.6...1.3.7
[1.3.6]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.3.5...1.3.6
[1.3.5]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.3.4...1.3.5
[1.3.4]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.3.3...1.3.4
[1.3.3]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.3.2...1.3.3
[1.3.2]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.3.1...1.3.2
[1.3.1]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.3.0...1.3.1
[1.3.0]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.2.4...1.3.0
[1.2.4]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.2.3...1.2.4
[1.2.3]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.2.2...1.2.3
[1.2.2]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.2.1...1.2.2
[1.2.1]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.1.9...1.2.0
[1.1.9]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.1.8...1.1.9
[1.1.8]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.1.7...1.1.8
[1.1.7]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.1.6...1.1.7
[1.1.6]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.1.5...1.1.6
[1.1.5]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.1.4...1.1.5
[1.1.4]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.1.3...1.1.4
[1.1.3]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.1.2...1.1.3
[1.1.2]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.1.1...1.1.2
[1.1.1]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.0.9...1.1.0
[1.0.9]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.0.8...1.0.9
[1.0.8]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.0.7...1.0.8
[1.0.7]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.0.6...1.0.7
[1.0.6]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.0.5...1.0.6
[1.0.5]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.0.4...1.0.5
[1.0.4]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.0.3...1.0.4
[1.0.3]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.0.2...1.0.3
[1.0.2]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.3.9...1.0.0
[0.3.9]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.3.8...0.3.9
[0.3.8]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.3.7...0.3.8
[0.3.7]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.3.6...0.3.7
[0.3.6]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.3.5...0.3.6
[0.3.5]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.3.4...0.3.5
[0.3.4]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.3.3...0.3.4
[0.3.3]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.3.2...0.3.3
[0.3.2]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.3.1...0.3.2
[0.3.1]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.3.0...0.3.1
[0.3.0]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.2.9...0.3.0
[0.2.9]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.2.8...0.2.9
[0.2.8]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.2.7...0.2.8
[0.2.7]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.2.6...0.2.7
[0.2.6]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.2.5...0.2.6
[0.2.5]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.2.4...0.2.5
[0.2.4]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.2.3...0.2.4
[0.2.3]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.2.2...0.2.3
[0.2.2]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.2.1...0.2.2
[0.2.1]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.2.0...0.2.1
[0.2.0]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.13...0.2.0
[0.1.13]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.12...0.1.13
[0.1.12]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.11...0.1.12
[0.1.11]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.10...0.1.11
[0.1.10]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.9...0.1.10
[0.1.9]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.8...0.1.9
[0.1.8]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.7...0.1.8
[0.1.7]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.6...0.1.7
[0.1.6]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.5...0.1.6
[0.1.5]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.4...0.1.5
[0.1.4]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.3...0.1.4
[0.1.3]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.2...0.1.3
[0.1.2]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.1...0.1.2
[0.1.1]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/compare/0.1.0...0.1.1
[0.1.0]: https://github.com/State-of-Rhode-Island-eCMS/ecms_distribution/releases/tag/0.1.0

---
title: Configuration Management
tags:
  - config
  - drupal
---
# Configuration Management

This project intends to be a Drupal distribution that is used with the
Acquia Site Factory product. This architecture relies on Drupal's multi-site
setup. This means that there could be any number of sites that rely on this
codebase. Because of this setup, you should _NOT_ be using
config export or import for managing individual site configurations.
All configuration is managed by the installation profile. Once a site is live
and using this distribution, that site's configuration will be maintained in
the database. Some pre-determined configuration will be maintained with the
Features module.

<?php

declare(strict_types=1);

/**
 * @file
 * Per-environment overrides for the Cloudflare cache purger.
 *
 * The Cloudflare API token must be set separately in each site's
 * secrets.settings.php (managed outside the repo):
 *
 *   putenv('CLOUDFLARE_API_TOKEN=your-token-here');
 *
 * The following env vars are read here and can be set as ACSF environment
 * variables or in a local .env / settings.local.php:
 *
 *   CLOUDFLARE_ZONE_ID          — Cloudflare zone ID for this environment.
 *   CLOUDFLARE_CACHE_TAG_PREFIX — Optional per-site prefix for cache tags so
 *                                  that purging Site A does not affect Site B
 *                                  when both share the same Cloudflare zone.
 *                                  Falls back to the site's hash_salt if unset.
 */

// Override the zone_id baked into recipe config with the env-specific value.
if (getenv('CLOUDFLARE_ZONE_ID')) {
  $config['cloudflare_purger.settings']['zone_id'] = getenv('CLOUDFLARE_ZONE_ID');
}

// Set a unique cache-tag prefix per site.
// Without this, a purge on one site would evict cached responses for *all*
// sites in the same zone that share identical Drupal cache tags (e.g. node:1).
if (getenv('CLOUDFLARE_CACHE_TAG_PREFIX')) {
  $settings['cloudflare_purger_cache_tag_prefix'] = getenv('CLOUDFLARE_CACHE_TAG_PREFIX');
}

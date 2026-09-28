# eCMS ops console app

A Symfony Console application for operating on Acquia Site Factory (ACSF)
sites: creating/pruning backups and running a post-upgrade site health
check. It replaces the old standalone `backup-rigov-prod.php` and
`post-upgrade-test.php` scripts.

For step-by-step usage instructions, see
[docs/ecms-cli-usage.md](../docs/ecms-cli-usage.md). This file covers
setup, the command reference, and running the test suite.

## Setup

```
cp deployment-scripts/.env.example deployment-scripts/.env
```

Edit `deployment-scripts/.env` (gitignored — never commit real credentials)
and set at least `ACSF_API_USER` and `ACSF_API_KEY`. Get your API key from
the ACSF dashboard: Account Settings > API Key, e.g.
`https://www.riecms.acsitefactory.com/user/{uid}/api-key`.

The same variables can instead be exported directly in your shell or
supplied as CI secrets — `deployment-scripts/.env` is loaded only if
present, and never overrides a variable already set in the environment.

## Environments

Every command accepts `--env=dev|test|prod` (default **`test`**) and
`--prod` as shorthand for `--env=prod`. **Production is never touched
unless explicitly requested.** A banner at the start of every run states
the resolved target.

To use a different key per environment (recommended for prod), set
`ACSF_API_KEY_DEV` / `ACSF_API_KEY_TEST` / `ACSF_API_KEY_PROD` instead of
(or in addition to) the bare `ACSF_API_KEY`; the per-environment variable
takes precedence for that environment.

## Commands

```
deployment-scripts/bin/ecms list
```

- **`ecms:backup:create [--sites=all] [--components=database]`**
  Requests a new backup for one or more sites.

  ```
  deployment-scripts/bin/ecms ecms:backup:create --prod --sites=111,222
  ```

- **`ecms:backup:prune [--sites=all] [--retention-days=30] [--dry-run]`**
  Deletes backups older than the retention window. Always review with
  `--dry-run` first. A non-interactive run (e.g. CI) requires `--force`;
  targeting `--prod` additionally requires
  `--i-know-this-is-production`, even with `--force`.

  ```
  deployment-scripts/bin/ecms ecms:backup:prune --dry-run
  deployment-scripts/bin/ecms ecms:backup:prune --prod --force --i-know-this-is-production
  ```

- **`ecms:site:status [--sites=all] [--path=/user] [--format=table|json] [--insecure]`**
  Checks that sites respond; useful as a post-upgrade smoke test. Exits
  non-zero if any site is unhealthy, so it can gate a deployment. Built
  with no ACSF API credentials at all — a site's own domain never sees the
  factory API key. `--insecure` skips TLS certificate verification for this
  check only (e.g. to troubleshoot a site with a known, temporary
  certificate problem); the authenticated ACSF API client always verifies
  TLS regardless of this flag.

  ```
  deployment-scripts/bin/ecms ecms:site:status --prod
  deployment-scripts/bin/ecms ecms:site:status --sites=111 --insecure
  ```

Composer alias: `composer ecms -- <command> [options]`.

## Development

```
composer install
vendor/bin/phpunit --testsuite deployment
composer validate:php
```

Tests run entirely against a Guzzle `MockHandler` — no network access and
no real credentials required.

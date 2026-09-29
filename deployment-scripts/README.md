# eCMS ops console app

A Symfony Console application for operating on Acquia Site Factory (ACSF)
sites: creating/pruning backups, running a post-upgrade site health check,
and running drush commands across sites over SSH. It replaces the old
standalone `backup-rigov-prod.php` and `post-upgrade-test.php` scripts.

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

## Prerequisites for `ecms:drush:run`

Every other command talks only to the ACSF REST API and needs nothing but
an API key. `ecms:drush:run` connects to the servers, so it needs two more
things set up once per machine.

**1. An Acquia SSH key.** Your public key must be on the Acquia account
with access to this subscription.

**2. The drush site aliases.** Download them from the Acquia Cloud UI —
your user profile > Credentials > Drush aliases — and copy the three files
for this subscription into `drush/sites/`:

```
drush/sites/01dev.site.yml
drush/sites/01test.site.yml
drush/sites/01live.site.yml
```

Each file needs a group (`'*'`, or the site's machine name) defining
`host`, `user`, `root`, `uri` and `paths.drush-script`:

```yaml
'*':
  uri: https://${env-name}.<factory-host>
  root: /var/www/html/<sitegroup>.<env>/docroot
  host: <sitegroup><env>.ssh.<realm>.acquia-sites.com
  user: <sitegroup>.<env>
  paths:
    drush-script: /var/www/html/<sitegroup>.<env>/vendor/bin/drush
```

**These files are gitignored and must never be committed.** This project is
open source and the aliases name SSH hosts and server paths. Confirm before
you commit anything:

```
git check-ignore -v drush/sites/01live.site.yml   # prints a .gitignore rule
git status --porcelain drush/sites/               # prints nothing
```

Use `--drush-sites-dir` only if your checkout keeps them elsewhere.

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

- **`ecms:drush:run [--cmd=...] [--sites=all] [--timeout=1800] [--dry-run]`**
  (alias `ecms:drush`) Runs an ordered sequence of drush commands on one or
  more sites over SSH. Repeat `--cmd` for a sequence; order is preserved.
  If a step fails, that site's remaining steps are skipped — use
  `--continue-on-error` to override — while other sites still run. Exits
  non-zero if any site failed. Needs the SSH key and site aliases described
  under **Prerequisites** above.

  Always `--dry-run` first: it resolves every site and prints the exact ssh
  command without connecting to anything.

  ```
  deployment-scripts/bin/ecms ecms:drush:run --env=test --sites=111 --cmd="cr"

  deployment-scripts/bin/ecms ecms:drush:run --prod --sites=all \
    --cmd="sapi-sis acquia_search_index searchstax" \
    --cmd="sapi-rt acquia_search_index" \
    --cmd="sapi-i acquia_search_index" \
    --force --i-know-this-is-production --log=/tmp/reindex.log
  ```

  Nothing is auto-confirmed: no `-y` is injected, because doing so on
  arbitrary operator-supplied commands would silently confirm things like
  `sql:drop`. Add `-y` to a `--cmd` yourself when a command needs it.

Composer alias: `composer ecms -- <command> [options]`.

## Development

```
composer install
vendor/bin/phpunit --testsuite deployment
composer validate:php
```

Tests run entirely against a Guzzle `MockHandler` — no network access and
no real credentials required.

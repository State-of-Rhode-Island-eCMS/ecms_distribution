# How to Use the eCMS Tool

This document tells you how to use the eCMS tool. Use the eCMS tool to
manage sites in Acquia Site Factory (ACSF). This document follows the
ASD-STE100 Simplified Technical English standard. Sentences are short.
Each step gives one instruction.

The eCMS tool replaces two old scripts: `backup-rigov-prod.php` and
`post-upgrade-test.php`. Do not use the old scripts. The files do not
exist anymore.

## 1. What the ACSF Tool Does

The eCMS tool is a command-line program. The program is in the
`deployment-scripts/` directory. Use the eCMS tool to do these tasks:

- Start a backup of one or more sites.
- Delete old backups.
- Check that sites respond to requests.

## 2. Terms Used in This Document

Read this list before you use the eCMS tool.

| Term | Meaning |
|---|---|
| ACSF | Acquia Site Factory. ACSF hosts many Drupal sites. |
| environment | A group of sites. There are three environments: `dev`, `test`, and `prod`. |
| `prod` | The production environment. Real users see sites in `prod`. |
| site ID | A number that identifies one site in ACSF. |
| backup | A saved copy of a site's data. |
| retention period | The number of days the eCMS tool keeps a backup. |
| dry run | A command that shows what will happen. A dry run does not change data. |
| API key | A secret code. The eCMS tool uses the API key to connect to ACSF. |

## 3. Safety Rules

Read these rules before you run a command.

1. The eCMS tool connects to the `test` environment by default. The
   eCMS tool does not connect to `prod` unless you add the `--prod`
   option.
2. Do not put your API key in a file that you commit to git. Store your
   API key in the file `deployment-scripts/.env`. Git ignores this file.
3. Before you delete backups, run the command with the `--dry-run`
   option first. Check the list of backups. Then run the command
   again without `--dry-run`.
4. To delete backups in `prod`, you must add two options:
   `--force` and `--i-know-this-is-production`. If you do not add both
   options, the eCMS tool will not delete the backups.

## 4. Before You Start

Complete these steps one time before you use the eCMS tool.

1. Open a terminal.
2. Go to the root directory of this project.
3. Run this command to install the required software:

   ```
   composer install
   ```

4. Copy the example settings file. Run this command:

   ```
   cp deployment-scripts/.env.example deployment-scripts/.env
   ```

5. Open the file `deployment-scripts/.env` in a text editor.
6. Find your API key. Go to the ACSF dashboard. Click **Account
   Settings**. Click **API Key**.
7. Add your ACSF username to the line that starts with `ACSF_API_USER=`.
8. Add your API key to the line that starts with `ACSF_API_KEY=`.
9. Save the file.

## 5. How to Run a Command

Every command has the same basic form:

```
deployment-scripts/bin/ecms <command> [options]
```

To see a list of all commands, run this command:

```
deployment-scripts/bin/ecms list
```

### 5.1 How to Choose an Environment

Every command connects to the `test` environment by default. To
connect to a different environment, add one of these options:

- Add `--env=dev` to connect to the `dev` environment.
- Add `--env=test` to connect to the `test` environment.
- Add `--env=prod` to connect to the `prod` environment.
- Add `--prod` to connect to the `prod` environment. `--prod` does the
  same thing as `--env=prod`.

### 5.2 How to Choose Sites

Every command works on one or more sites. To choose sites, add the
`--sites` option:

- To run a command on all sites, do not add the `--sites` option. Or,
  add `--sites=all`.
- To run a command on specific sites, add `--sites=` and the site IDs.
  Separate the site IDs with a comma. Example: `--sites=111,222,333`.

## 6. How to Check That Sites Respond

Use the `ecms:site:status` command to check that sites respond to
requests. Run this command after you upgrade a site.

1. Run this command:

   ```
   deployment-scripts/bin/ecms ecms:site:status --env=test
   ```

2. Read the table in the output.
3. If a site shows "DOWN", the site did not respond correctly.
4. If any site is down, the eCMS tool ends with an error code. You can
   use this error code to stop a deployment script.

To check specific sites only, add the `--sites` option:

```
deployment-scripts/bin/ecms ecms:site:status --sites=111,222
```

## 7. How to Start a Backup

Use the `ecms:backup:create` command to start a new backup.

1. Run this command:

   ```
   deployment-scripts/bin/ecms ecms:backup:create --env=test --sites=111
   ```

2. Read the output. The output shows a task ID for each site.
3. To back up all sites in the `prod` environment, run this command:

   ```
   deployment-scripts/bin/ecms ecms:backup:create --prod
   ```

By default, the eCMS tool backs up only the database. To back up other
parts of a site, add the `--components` option. Separate each
component with a comma. Example:

```
deployment-scripts/bin/ecms ecms:backup:create --sites=111 --components="database,public files"
```

Put quotation marks around the value if a component name has a space in
it.

## 8. How to Delete Old Backups

Use the `ecms:backup:prune` command to delete backups that are older
than a retention period. Follow these steps in order.

1. Run the command with the `--dry-run` option first:

   ```
   deployment-scripts/bin/ecms ecms:backup:prune --env=test --retention-days=30 --dry-run
   ```

2. Read the table in the output. The table lists the backups that the
   eCMS tool will delete.
3. If the list is correct, run the command again. Remove the
   `--dry-run` option:

   ```
   deployment-scripts/bin/ecms ecms:backup:prune --env=test --retention-days=30
   ```

4. The eCMS tool asks you to confirm the deletion. Type `yes` to
   continue.

### 8.1 How to Delete Old Backups Without a Prompt

To run the command without a confirmation prompt, add the `--force`
option:

```
deployment-scripts/bin/ecms ecms:backup:prune --env=test --retention-days=30 --force
```

Use `--force` in scripts that run without a person to answer the
prompt.

### 8.2 How to Delete Old Backups in Production

**WARNING: This procedure deletes data. Data that is deleted cannot be
restored. Do a dry run first. See section 8.**

To delete backups in the `prod` environment, add three options:

```
deployment-scripts/bin/ecms ecms:backup:prune --prod --force --i-know-this-is-production
```

If you do not add `--i-know-this-is-production`, the eCMS tool stops.
The eCMS tool does not delete any backups.

## 9. Common Errors

This section lists common errors and how to correct them.

**Error: "Missing required environment variable(s)"**

The file `deployment-scripts/.env` does not have your API key, or the
API key is empty. Go to section 4. Add your API key to the file.

**Error: "Unknown environment"**

You typed the environment name incorrectly. Correct values are `dev`,
`test`, and `prod`.

**Error: "The --prod flag conflicts with --env=..."**

You added `--prod` and `--env` together, and the values do not match.
Remove one of the two options.

**Error: "Invalid site ID(s) in --sites"**

You typed a site ID incorrectly. A site ID must be a whole number
greater than zero.

**Error: "Refusing to run a destructive command against production"**

You tried to delete backups in `prod` without the
`--i-know-this-is-production` option. Go to section 8.2.

## 10. Where to Find More Information

- For the full list of options for a command, add `--help` after the
  command name. Example: `deployment-scripts/bin/ecms ecms:backup:prune --help`.
- For information about the code, read `deployment-scripts/README.md`.
- For information about the ACSF API, go to the ACSF API documentation
  at `https://www.riecms.acsitefactory.com/api/v1`.

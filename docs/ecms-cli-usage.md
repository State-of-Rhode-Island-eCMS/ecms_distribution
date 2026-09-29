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
- Run drush commands on one or more sites.

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
| TLS certificate | A file that proves a site is the correct site. A browser or tool checks this file before it trusts a site. |
| drush | A command-line tool that controls one Drupal site. |
| SSH | A secure connection to a server. You need an SSH key to use it. |
| site alias | A file that tells the eCMS tool how to connect to an environment. You download this file from Acquia. |
| sequence | A list of drush commands. The eCMS tool runs them in the order you give. |
| step | One drush command in a sequence. |
| index | A list that makes site search work. A site must build its index before search works. |
| timeout | The maximum number of seconds one step can run. |

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
5. Before you run drush commands, run the command with the `--dry-run`
   option first. Read the list of sites. Then run the command again
   without `--dry-run`.
6. To run drush commands in `prod`, you must add the same two options:
   `--force` and `--i-know-this-is-production`.
7. If one step fails on a site, the eCMS tool does not run the remaining
   steps on that site. It continues with the other sites.
8. Do not add the site alias files to git. Git ignores these files. This
   project is open source. The files contain server names and server
   paths.

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

### 4.1 Extra Setup for Drush Commands

Do these steps only if you will use the `ecms:drush:run` command. The other
commands do not need them.

1. Make sure your SSH key is on your Acquia account. Ask your
   administrator if you are not sure.
2. Open the Acquia Cloud website. Sign in.
3. Open your user profile. Click **Credentials**.
4. Find **Drush aliases**. Download the file.

   Acquia moves this page sometimes. If you cannot find it, search the
   Acquia documentation for "download Drush aliases".

5. Open the downloaded archive.
6. Copy three files into the `drush/sites/` directory of this project.
   Give them these exact names:

   ```
   drush/sites/01dev.site.yml
   drush/sites/01test.site.yml
   drush/sites/01live.site.yml
   ```

7. Run this command to make sure git ignores the files:

   ```
   git check-ignore -v drush/sites/01live.site.yml
   ```

8. The command must print a rule. If it prints nothing, stop. Tell your
   administrator. **Do not commit these files.**

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

### 6.1 How to Check a Site With a Certificate Problem

Sometimes a site has a TLS certificate problem. Example: the
certificate is new and has not finished setup. In this case, the
command shows an error for that site.

To skip the certificate check for this command only, add the
`--insecure` option:

```
deployment-scripts/bin/ecms ecms:site:status --sites=111 --insecure
```

**WARNING: The `--insecure` option turns off a safety check. Use this
option only to find a known, temporary problem. Do not use this
option for a normal check.**

The `--insecure` option does not change how the eCMS tool connects to
the ACSF API. The eCMS tool always checks the certificate for the
ACSF API.

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

## 9. How to Run Drush Commands on Sites

Use the `ecms:drush:run` command to run drush commands on sites. Complete
section 4.1 first.

Each `--cmd` option is one drush command. The eCMS tool runs the commands
in the order you write them.

### 9.1 How to Do a Dry Run

Always do a dry run first. A dry run does not connect to any server.

1. Run this command:

   ```
   deployment-scripts/bin/ecms ecms:drush:run --env=test --sites=all \
     --cmd="sapi-i acquia_search_index" --dry-run
   ```

2. Read the list of sites. Make sure the list is correct.
3. Read the example SSH command. Make sure the web address is correct.

### 9.2 How to Run One Command

1. Run this command:

   ```
   deployment-scripts/bin/ecms ecms:drush:run --env=test --sites=111 --cmd="cr"
   ```

2. The eCMS tool asks you to confirm. Type `yes` to continue.

### 9.3 How to Re-index Sites for Search

Run this sequence after a release that changes the search server.

1. Do a dry run first. See section 9.1.
2. Run this command for the test environment:

   ```
   deployment-scripts/bin/ecms ecms:drush:run --env=test --sites=all \
     --cmd="sapi-sis acquia_search_index searchstax" \
     --cmd="sapi-rt acquia_search_index" \
     --cmd="sapi-i acquia_search_index" \
     --log=/tmp/reindex-test.log
   ```

3. Read the results table. Make sure every step shows `OK`.
4. Run the same command for production. Add two options:

   ```
   deployment-scripts/bin/ecms ecms:drush:run --prod --sites=all \
     --cmd="sapi-sis acquia_search_index searchstax" \
     --cmd="sapi-rt acquia_search_index" \
     --cmd="sapi-i acquia_search_index" \
     --force --i-know-this-is-production --log=/tmp/reindex-prod.log
   ```

**Note:** each command names the index `acquia_search_index`. If you do not
name an index, drush works on every index on the site. That takes much
longer.

### 9.4 How to Read the Results

The eCMS tool prints a table at the end. Each row is one step on one site.

| Result | Meaning |
|---|---|
| `OK` | The step finished correctly. |
| `FAILED` | The step did not finish. Read the Error column. |
| `TIMED OUT` | The step ran longer than the timeout. |
| `SKIPPED` | An earlier step on that site failed, so this step did not run. |

If any site fails, the eCMS tool ends with an error code. You can use this
error code to stop a deployment script.

### 9.5 How to Change the Timeout

One step can run for 1800 seconds (30 minutes) by default. A large site
index can take longer.

To give each step more time, add the `--timeout` option:

```
deployment-scripts/bin/ecms ecms:drush:run --env=test --sites=111 \
  --cmd="sapi-i acquia_search_index" --timeout=3600
```

### 9.6 How to Save the Output to a File

A production run can take hours. Add the `--log` option to save all output
to a file:

```
deployment-scripts/bin/ecms ecms:drush:run --prod --sites=all \
  --cmd="cr" --force --i-know-this-is-production --log=/tmp/run.log
```

The log file is plain text. It does not contain colour codes.

## 10. Common Errors

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

**A site shows "DOWN" with an SSL or certificate error**

The site has a TLS certificate problem. Go to section 6.1.

**Error: "--cmd is required"**

You did not give a drush command. Add at least one `--cmd="..."` option.

**Error: "Invalid drush command in --cmd"**

The value must start with a drush command name. Shell characters such as
`;` and `&&` are not allowed, and the value cannot start with an option
such as `-v`. Write one `--cmd` for each command instead.

**Error: "No drush site alias found"**

You did not download the site alias files, or they are in the wrong place.
Go to section 4.1. The message shows the exact file path the eCMS tool
looked for.

**Error: "Site alias ... is missing required key"**

The alias file is incomplete. The message names the file, the group and the
missing key. Download the file again from Acquia. Go to section 4.1.

**Error: "Site alias ... declares ac-env ... but the target is ..."**

You copied the wrong environment's file. For example, the `01test` file is
saved as `01live.site.yml`. Go to section 4.1 and copy the correct file.

**Error: "Could not parse site alias"**

The alias file is damaged. Download it again from Acquia.

**Error: "Permission denied (publickey)"**

Your SSH key is not on your Acquia account. Ask your administrator to add
it. Then try again.

**Error: "Host key verification failed"**

Your computer does not know this server yet. Connect to the server once by
hand to accept its key. Do not turn off this check.

**Error: "Cannot run drush on ..."**

The eCMS tool connected but could not run drush. Check that your SSH key
works and that the alias file names the correct server.

**A step shows "TIMED OUT"**

The command ran longer than the timeout. Go to section 9.5 to raise the
timeout. Or run fewer sites at one time.

**A step shows "SKIPPED"**

An earlier step on that site failed. Read the Error column for that site.
Correct that problem first.

**Error: "--timeout must be a positive integer"**

The `--timeout` value must be a whole number greater than zero.

**Error: "--log directory ... does not exist or is not writable"**

Create the directory first, or choose a different path for `--log`.

**Error: "Refusing to run against ... sites"**

You asked for more sites than the limit. Use `--sites` to run a smaller
group at one time.

## 11. Where to Find More Information

- For the full list of options for a command, add `--help` after the
  command name. Example: `deployment-scripts/bin/ecms ecms:backup:prune --help`.
- For information about the code, read `deployment-scripts/README.md`.
- For information about the ACSF API, go to the ACSF API documentation
  at `https://www.riecms.acsitefactory.com/api/v1`.

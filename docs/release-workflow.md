## Release workflow

### Versioning scheme
[eCMS Distribution][] uses a modified [Semantic Versioning][] scheme.
The only changes we make are for clarity. Since we're
versioning a website, and not a library or piece of software, we use different
terms to describe each part of the version string.

Given a version number `SITE.MAJOR.MINOR`, increment the:

1. `SITE` version when you rebuild the website,
2. `MAJOR` version when you make a major change to the website,
3. `MINOR` version when you make a minor change to the website.

Additional labels for pre-release and build metadata are available as extensions
to the `SITE.MAJOR.MINOR` format.

#### Major vs Minor

##### Major
- _Minor_ (in the semver sense) core updates
- _Major_ (in the semver sense) module updates
- Major feature changes to the website

##### Minor
- _Patch_ (in the semver sense) core updates
- All other module updates
- Bug fixes
- Minor feature changes

For any version change, it's up to the Release Manager/Project Lead and the
Project Manager to decide if the change set warrants a major or minor version
change. If there is any question, a discussion should also be opened up amongst
the engineering team.

### Creating a release
When it's time to release a new set of code to production, the Release Manager
(usually the Project Lead) will coordinate with the Project Manager and create
a release.

#### Create a release branch
The first step of releasing is creating a release branch. In our examples, we
will be releasing version `2.5.1`.

Release branches should always be prefixed with `release/`.

```
git checkout master
git pull
git checkout -b release/2.5.1
git push -u origin release/2.5.1
```

#### Update the `CHANGELOG`
Next, you must update the `CHANGELOG` to reflect the next version.

1. Remove any empty sections under under the `[Unreleased]` version and make any
updates to the information that you see fit.

2. Change `[Unreleased]` to `[2.5.1] - 2017-04-28`. Replacing `2017-04-28` with
the current date in the format `YYYY-MM-DD`.

3. Add a new unreleased section with the following format above the
`[2.5.1] - 2017-04-28` release.

```
## [Unreleased]
### Added
### Changed
### Deprecated
### Removed
### Fixed
### Security
```

4. Update the `[Unreleased]` comparison link at the bottom of the file, and add
a new comparison link below it comparing between the previous version and the
new version being released.

```
[Unreleased]: https://github.com/oomphinc/project/compare/2.5.1...HEAD
[2.5.1]: https://github.com/oomphinc/project/compare/2.5.0...2.5.1
```

5. Commit the `CHANGELOG` and push it up to GitHub.

```
git add CHANGELOG.md
git commit -m "Prepare CHANGELOG for 2.5.1 release"
git push
```

#### Release preparation
At this point, you should also make any changes necessary for the release to
proceed. For example, on a Drupal 8 site, you might want to pull down the
production configuration with Drush so that it's synchronized. This is where
that step would happen.

#### Create a pull request
Now we need to create a Pull Request for our release. This is just the last line
of defense in case we've made any mistakes. Just create a Pull Request against
`master` to merge in `release/2.5.1`.

Once that has been reviewed, approved, and merged...

#### Tag the release
Finally, we tag our release. All tags must be annotated and should contain the
relevant information from the `CHANGELOG` for this release. It is also
recommended that you sign your tags with your gpg key.

```
git checkout master
git pull
git tag -a 2.5.1
```

(use `-s` instead of `-a` to sign your tag with your gpg key)

This will open a text editor, and allow you to annotate the tag. The first line
of the tag annotation should be `Release version 2.5.1`, followed by an empty
line and then the relevant `CHANGELOG`. Once complete, your tag annotation
should look like this:

```
Release version 2.5.1
## [2.5.1] - 2017-04-28
### Added
- Added an additional feature
### Fixed
- Fixed a bug that did xyz
```

Finally, push it up to GitHub:

```
git push --follow-tags
```

## Backing up site databases

Add the following PHP script named backup.php to the develop-ecms-profile directory
to initiate backups of all production databases.
This requires an Acquia Cloud API key, which you can find in the Acquia Site Factory
dashboard under Account Settings > API Key.
E.g. https://www.riecms.acsitefactory.com/user/{uid}/api-key

```phpregexp
#!/usr/bin/env php
<?php

/*
curl https://www.riecms.acsitefactory.com/api/v1/sites \
-v -u ${user}:${api_key} -k -X POST \
-H 'Content-Type: application/json'

*/

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Example script for making backups of several sites through the REST API.
// Two things are left up to the script user:
// - Including Guzzle, which is used by request();
//   e.g. by doing: 'composer init; composer require guzzlehttp/guzzle'
require 'vendor/autoload.php';

// - Populating $config:
$config = [
  // URL of a subsection inside the SF REST API; must end with sites/.
  'url' => 'https://www.riecms.acsitefactory.com/api/v1/sites/',
  'api_user' => 'YOUR_ACQUIA_USER_ACCOUNT_NAME',
  'api_key' => 'YOUR_ACQUIA_CLOUD_API_KEY',

  // Site IDs of the sites to process; can also be provided as CLI argument.
  'sites' => ['all'],

  // Number of days before backups are deleted; can also be provided on ClI.
  'backup_retention' => 30,

  // Request parameter for /api/v1#List-sites.
  'limit' => 100,

  // The components of the websites to backup.
  // Details: /api/v1#Create-a-site-backup.
  // 'codebase' is excluded from the default components since those files would
  // be the same in each site backup, and cannot be restored into the factory.
  'components' => ['database', 'public files', 'private files', 'themes'],
];

if ($argc < 2 || $argc > 4 || !in_array($argv[1], array('backup-add', 'backup-del'), TRUE)) {
  $help = <<<EOT
    Usage: php application.php parameter [sites] [backup_retention=30].
    Where:
    - parameter is one of {backup-add, backup-del}
    - [sites] is be either a comma separated list (e.g. 111,222,333) or 'all'
    - [backup_retention] the number of days for which the backups should be retained. If passed this threshold they will be deleted when using backup-del command (defaults to 30 days)

EOT;
  echo $help;
  exit(1);
}

// Lower the 'limit' parameter to the maximum which the API allows.
if ($config['limit'] > 100) {
  $config['limit'] = 100;
}

// Check if the list of sites in $config is to be overridden by the provided
// input. If the input is set to 'all' then fetch the list of sites using the
// Site Factory API, otherwise it should be a comma separated list of site IDs.
if ($argc >= 3) {
  if ($argv[2] == 'all') {
    $config['sites'] = get_all_sites($config);
  }
  else {
    // Removing spaces.
    $no_spaces = str_replace(' ', '', $argv[2]);

    // Keeping only IDs that are valid.
    $config['sites'] = array_filter(explode(',', $no_spaces), "id_check");

    // Removing duplicates.
    $config['sites'] = array_unique($config['sites']);
  }
}

// Check if the backup_retention parameter is overwritten.
if ($argc >= 4 && id_check($argv[3])) {
  $config['backup_retention'] = $argv[3];
}

// Helper; returns true if given ID is valid (numeric and > 0), false otherwise.
function id_check($id) {
  return is_numeric($id) && $id > 0;
}

// Fetches the list of all sites using the Site Factory REST API.
function get_all_sites($config) {
  // Starting from page 1.
  $page = 1;

  $sites = array();

  printf("Getting all sites - Limit / request: %d\n", $config['limit']);

  // Iterate through the paginated list until we get all sites, or
  // an error occurs.
  do {
    printf("Getting sites page: %d\n", $page);

    $method = 'GET';
    $url = $config['url'] . "?limit=" . $config['limit'] . "&page=" . $page;
    $has_another_page = FALSE;
    $res = request($url, $method, $config);

    if ($res->getStatusCode() != 200) {
      echo "Error whilst fetching site list!\n";
      exit(1);
    }

    $next_page_header = $res->getHeader('link');
    $response = json_decode($res->getBody()->getContents());

    // If the next page header is present and has a "next" link, we know we
    // have another page.
    if (!empty($next_page_header) && strpos($next_page_header[0], 'rel="next"') !== FALSE) {
      $has_another_page = TRUE;
      $page++;
    }

    foreach ($response->sites as $site) {
      $sites[] = $site->id;
    }
  } while ($has_another_page);

  return $sites;
}

// Helper function to return API user and key.
function get_request_auth($config) {
  return [
    'auth' => [$config['api_user'], $config['api_key']],
  ];
}

// Sends a request using the guzzle HTTP library; prints out any errors.
function request($url, $method, $config, $form_params = []) {
  // We are setting http_errors => FALSE so that we can handle them ourselves.
  // Otherwise, we cannot differentiate between different HTTP status codes
  // since all 40X codes will just throw a ClientError exception.
  $client = new Client(['http_errors' => FALSE]);

  $parameters = get_request_auth($config);
  if ($form_params) {
    $parameters['form_params'] = $form_params;
  }

  try {
    $res = $client->request($method, $url, $parameters);
    return $res;
  }
  catch (RequestException $e) {
    printf("Request exception!\nError message %s\n", $e->getMessage());
  }

  return NULL;
}

// Iterates through backups for a certain site and deletes them if they are
// past the backup_retention mark.
function backup_del($backups, $site_id, $config) {
  // Iterating through existing backups for current site and deleting those
  // that are X days old.
  $time = $config['backup_retention'] . ' days ago';
  foreach ($backups as $backup) {
    $timestamp = $backup->timestamp;
    if ($timestamp < strtotime($time)) {
      printf("Deleting %s with backup (ID: %d).\n", $backup->label, $backup->id);

      $method = 'DELETE';
      $url = $config['url'] . $site_id . '/backups/' . $backup->id;

      $res = request($url, $method, $config);
      if (!$res || $res->getStatusCode() != 200) {
        printf("Error! Whilst deleting backup ID %d. Please check the above messages for the full error.\n", $backup->id);
        continue;
      }
      $task = json_decode($res->getBody()->getContents())->task_id;
      printf("Deleting backup (ID: %d) with task ID %d.\n", $backup->id, $task);
    }
    else {
      printf("Keeping %s since it was created sooner than %s (ID: %d).\n", $backup->label, $time, $backup->id);
    }
  }
}

// Creates or deletes backups depending on the operation given.
function backup($operation, $config) {
  // Setting global operation endpoints and messages.
  if ($operation === 'backup-add') {
    $endpoint = '/backup';
    $message = "Creating backup for site ID %d.\n";
    $method = 'POST';
    $form_params = [
      'components' => $config['components'],
    ];
  }
  else {
    // Unlike in other code, we do not paginate through backups, but we get the
    // maximum for one request.
    $endpoint = '/backups?limit=100';
    $message = "Retrieving old backups for site ID %d.\n";
    $method = 'GET';
    $form_params = [];
  }

  // Iterating through the list of sites defined in secrets.php.
  for ($i = 0; $i < count($config['sites']); $i++) {
    // Sending API request.
    $url = $config['url'] . $config['sites'][$i] . $endpoint;
    $res = request($url, $method, $config, $form_params);

    $message_site = sprintf($message, $config['sites'][$i]);
    // If request returned an error, we show that and
    // we continue with another site.
    if (!$res) {
      // An exception was thrown.
      printf('Error whilst %s', $message_site);
      printf("Please check the above messages for the full error.\n");
      continue;
    }
    elseif ($res->getStatusCode() != 200) {
      // If a site has no backups, it will return a 404.
      if ($res->getStatusCode() == 404 && $operation == 'backup-del') {
        printf("Site ID %d has no backups.\n", $config['sites'][$i]);
      }
      else {
        printf('Error whilst %s', $message_site);
        printf("HTTP code %d\n", $res->getStatusCode());
        $body = json_decode($res->getBody()->getContents());
        printf("Error message: %s\n", $body ? $body->message : '<empty>');
      }
      continue;
    }

    // All good here.
    echo $message_site;

    // For deleting backups, we have to iterate through the backups we get.
    if ($operation == 'backup-del') {
      backup_del(json_decode($res->getBody()->getContents())->backups, $config['sites'][$i], $config);
    }
  }
}

backup($argv[1], $config);
```

Execute the script in the develop-ecms-profile project.
`php backup.php backup-add all`

[Oomph Inc's Drupal Scaffold]: https://github.com/oomphinc/drupal-scaffold.git
[Keep a Changelog]: http://keepachangelog.com/
[git-commit-messages]: https://chris.beams.io/posts/git-commit/
[Semantic Versioning]: http://semver.org/
[Release workflow]: #release-workflow

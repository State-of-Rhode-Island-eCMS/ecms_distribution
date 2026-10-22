<?php

declare(strict_types=1);

namespace Ecms\Deployment\Remote;

use Ecms\Deployment\Config\FactoryEnvironment;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Reads drush site alias files and turns them into SiteAlias objects.
 *
 * A present, valid alias file is a hard prerequisite for ecms:drush:run.
 * The files are downloaded per operator from the Acquia Cloud UI and are
 * gitignored, so every failure here must say exactly what is wrong and
 * where — an operator hitting this has nothing else to go on.
 *
 * Every exception is thrown before any SSH connection and before any ACSF
 * API call.
 */
final class SiteAliasLoader {

  /**
   * Top-level keys every alias group must define.
   *
   * 'paths.drush-script' is required too, but is nested, so it is validated
   * separately rather than smuggled into this list as a dotted string.
   */
  public const REQUIRED_KEYS = ['host', 'user', 'root', 'uri'];

  /**
   * The wildcard group key, which drush treats as a fallback.
   */
  private const WILDCARD = '*';

  /**
   * Parsed alias files, keyed by absolute path.
   *
   * Resolution happens once per site, so the file is read once and reused.
   *
   * @var array<string, array<string, mixed>>
   */
  private array $parsed = [];

  /**
   * Constructs a SiteAliasLoader.
   *
   * @param string $sitesDirectory
   *   The directory holding {01dev,01test,01live}.site.yml.
   */
  public function __construct(private readonly string $sitesDirectory) {}

  /**
   * The path load() would read for an environment.
   */
  public function pathFor(FactoryEnvironment $environment): string {
    return sprintf(
      '%s/%s.site.yml',
      rtrim($this->sitesDirectory, '/'),
      $environment->acquiaEnvironment()
    );
  }

  /**
   * Fails fast if the alias file is unusable, without picking a group.
   *
   * Deliberately does NOT require a '*' group. Drush treats '*' as a
   * fallback, not a requirement — SiteAliasFileLoader::getRequestedEnvData()
   * returns a named group first and only then falls back to '*' — so a file
   * enumerating named site groups is perfectly valid. Requiring '*' here
   * would reject a whole factory. Which group serves which site is decided
   * per site in load().
   *
   * @throws \InvalidArgumentException
   *   If the file is missing, unreadable, unparseable, defines no group
   *   with the required keys, or has a group whose ac-env names a
   *   different environment.
   */
  public function assertUsable(FactoryEnvironment $environment): void {
    $groups = $this->read($environment);
    $path = $this->pathFor($environment);

    $usable = [];
    $problems = [];
    foreach ($groups as $name => $group) {
      $this->assertEnvironmentMatches($group, $environment, $path, (string) $name);
      $missing = $this->missingKeys($group);
      if ($missing === []) {
        $usable[] = $name;
        continue;
      }
      $problems[] = sprintf('"%s" (missing %s)', $name, implode(', ', $missing));
    }

    if ($usable === []) {
      throw new \InvalidArgumentException(sprintf(
        "No usable group in site alias %s.\nEvery group is incomplete: %s.\nEach group needs: %s, paths.drush-script.",
        $path,
        $problems === [] ? 'the file defines none' : implode('; ', $problems),
        implode(', ', self::REQUIRED_KEYS)
      ));
    }
  }

  /**
   * The group names an alias file defines, for error messages.
   *
   * @return string[]
   *   The group keys, in file order.
   */
  public function groupsIn(FactoryEnvironment $environment): array {
    return array_keys($this->read($environment));
  }

  /**
   * Resolves the alias for one site.
   *
   * Prefers a group keyed by the site's machine name, falling back to the
   * '*' wildcard — the same precedence drush applies. Matching is
   * case-insensitive because ACSF site keys are not reliably lower case.
   *
   * @param \Ecms\Deployment\Config\FactoryEnvironment $environment
   *   The environment whose alias file to read.
   * @param string|null $siteName
   *   The site's machine name, or NULL to resolve the wildcard group only.
   *
   * @throws \InvalidArgumentException
   *   If no group matches, or the matching group is incomplete.
   */
  public function load(FactoryEnvironment $environment, ?string $siteName = NULL): SiteAlias {
    $groups = $this->read($environment);
    $path = $this->pathFor($environment);

    $key = $this->matchGroup($groups, $siteName);
    if ($key === NULL) {
      throw new \InvalidArgumentException(sprintf(
        'Site alias %s defines no group for %s. Groups present: %s.',
        $path,
        $siteName === NULL ? 'the "*" wildcard' : sprintf('site "%s" and no "*" wildcard', $siteName),
        implode(', ', array_keys($groups)) ?: 'none'
      ));
    }

    $group = $groups[$key];
    $missing = $this->missingKeys($group);
    if ($missing !== []) {
      throw new \InvalidArgumentException(sprintf(
        'Site alias %s group "%s" is missing required key(s): %s.',
        $path,
        $key,
        implode(', ', $missing)
      ));
    }

    $this->assertEnvironmentMatches($group, $environment, $path, $key);

    return new SiteAlias(
      host: (string) $group['host'],
      user: (string) $group['user'],
      docroot: (string) $group['root'],
      drushScript: (string) $group['paths']['drush-script'],
      uriTemplate: (string) $group['uri'],
      acSite: isset($group['ac-site']) ? (string) $group['ac-site'] : NULL,
      acRealm: isset($group['ac-realm']) ? (string) $group['ac-realm'] : NULL,
    );
  }

  /**
   * Reads and caches one alias file.
   *
   * @return array<string, mixed>
   *   The alias groups, keyed by group name.
   */
  private function read(FactoryEnvironment $environment): array {
    $path = $this->pathFor($environment);

    if (isset($this->parsed[$path])) {
      return $this->parsed[$path];
    }

    if (!is_file($path)) {
      throw new \InvalidArgumentException(sprintf(
        "No drush site alias found for the \"%s\" environment.\n\n  Expected: %s\n\n"
        . "Download the Drush aliases from Acquia Cloud (your user profile >\n"
        . "Credentials), then copy the %s environment's file to that path.\n"
        . "It must define a group ('*' or the site name) with these keys:\n\n"
        . "  %s, paths.drush-script\n\n"
        . "Do not commit it. See docs/ecms-cli-usage.md.",
        $environment->value,
        $path,
        $environment->acquiaEnvironment(),
        implode(', ', self::REQUIRED_KEYS)
      ));
    }

    if (!is_readable($path)) {
      throw new \InvalidArgumentException(sprintf('Site alias %s is not readable.', $path));
    }

    try {
      $data = Yaml::parseFile($path);
    }
    catch (ParseException $e) {
      throw new \InvalidArgumentException(sprintf(
        'Could not parse site alias %s: %s',
        $path,
        $e->getMessage()
      ), 0, $e);
    }

    if (!is_array($data) || $data === []) {
      throw new \InvalidArgumentException(sprintf(
        'Site alias %s is empty or is not a YAML mapping of alias groups.',
        $path
      ));
    }

    // Drop anything that is not a group mapping, e.g. a stray scalar.
    $groups = array_filter($data, 'is_array');
    $this->parsed[$path] = $groups;

    return $groups;
  }

  /**
   * Picks the group serving a site: exact name first, then the wildcard.
   *
   * @param array<string, mixed> $groups
   *   The parsed alias groups.
   * @param string|null $siteName
   *   The site machine name, or NULL for the wildcard only.
   *
   * @return string|null
   *   The matching group key, or NULL if none matches.
   */
  private function matchGroup(array $groups, ?string $siteName): ?string {
    if ($siteName !== NULL && $siteName !== '') {
      foreach (array_keys($groups) as $key) {
        if (strcasecmp((string) $key, $siteName) === 0) {
          return (string) $key;
        }
      }
    }

    return array_key_exists(self::WILDCARD, $groups) ? self::WILDCARD : NULL;
  }

  /**
   * The required keys a group is missing, including the nested drush script.
   *
   * @param mixed $group
   *   A parsed alias group.
   *
   * @return string[]
   *   The missing key names.
   */
  private function missingKeys(mixed $group): array {
    if (!is_array($group)) {
      return [...self::REQUIRED_KEYS, 'paths.drush-script'];
    }

    $missing = [];
    foreach (self::REQUIRED_KEYS as $key) {
      if (!isset($group[$key]) || !is_scalar($group[$key]) || (string) $group[$key] === '') {
        $missing[] = $key;
      }
    }

    $script = $group['paths']['drush-script'] ?? NULL;
    if (!is_string($script) || $script === '') {
      $missing[] = 'paths.drush-script';
    }

    return $missing;
  }

  /**
   * Refuses a file whose ac-env names a different environment.
   *
   * Catches the common slip of holding 01test content in 01live.site.yml,
   * where the banner would claim prod while the run hit test.
   *
   * @param array<string, mixed> $group
   *   The matched alias group.
   * @param \Ecms\Deployment\Config\FactoryEnvironment $environment
   *   The environment being targeted.
   * @param string $path
   *   The alias file path, for the message.
   * @param string $key
   *   The group key, for the message.
   */
  private function assertEnvironmentMatches(array $group, FactoryEnvironment $environment, string $path, string $key): void {
    $declared = $group['ac-env'] ?? NULL;
    if (!is_string($declared) || $declared === '') {
      return;
    }

    if (strcasecmp($declared, $environment->acquiaEnvironment()) !== 0) {
      throw new \InvalidArgumentException(sprintf(
        'Site alias %s group "%s" declares ac-env "%s" but the target is "%s". This looks like the wrong environment\'s file.',
        $path,
        $key,
        $declared,
        $environment->acquiaEnvironment()
      ));
    }
  }

}

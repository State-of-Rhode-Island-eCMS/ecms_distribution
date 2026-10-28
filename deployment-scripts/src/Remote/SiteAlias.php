<?php

declare(strict_types=1);

namespace Ecms\Deployment\Remote;

/**
 * The SSH connection details for one environment, read from a drush alias.
 *
 * Every value here comes from drush/sites/{01dev,01test,01live}.site.yml,
 * which operators download from the Acquia Cloud UI and never commit — this
 * project is open source and those files name SSH hosts and server paths.
 * Nothing about the connection is hardcoded in PHP.
 *
 * @see \Ecms\Deployment\Remote\SiteAliasLoader
 */
final class SiteAlias {

  /**
   * Matches a ${...} interpolation token in an alias uri template.
   */
  private const TOKEN_PATTERN = '/\$\{([a-z0-9_-]+)\}/i';

  /**
   * The only interpolation token this tool understands.
   *
   * In drush alias terms the group key is the "env" of @site.env, which for
   * an ACSF factory is the site's machine name — so ${env-name} resolves to
   * exactly that.
   */
  private const SITE_NAME_TOKEN = 'env-name';

  /**
   * Constructs a SiteAlias.
   *
   * @param string $host
   *   The SSH host, from the alias 'host' key.
   * @param string $user
   *   The SSH user, from the alias 'user' key.
   * @param string $docroot
   *   The remote Drupal root, from the alias 'root' key; passed as --root.
   * @param string $drushScript
   *   The remote drush executable, from 'paths.drush-script'.
   * @param string $uriTemplate
   *   The alias 'uri' value, still containing ${env-name}.
   * @param string|null $acSite
   *   The alias 'ac-site' value, if present. Audit output only.
   * @param string|null $acRealm
   *   The alias 'ac-realm' value, if present. Audit output only.
   */
  public function __construct(
    public readonly string $host,
    public readonly string $user,
    public readonly string $docroot,
    public readonly string $drushScript,
    public readonly string $uriTemplate,
    public readonly ?string $acSite = NULL,
    public readonly ?string $acRealm = NULL,
  ) {}

  /**
   * The SSH destination, e.g. "sitegroup.01live@sitegroup01live.ssh...".
   */
  public function userAtHost(): string {
    return sprintf('%s@%s', $this->user, $this->host);
  }

  /**
   * Resolves this alias's uri template for one site.
   *
   * The substituted name is lower-cased: ACSF site keys are not reliably
   * lower case, hostnames are case-insensitive to DNS, but Drupal's
   * multisite lookup matches sites.php keys as plain strings — so a
   * mixed-case host can miss the mapping and bootstrap the *default* site
   * instead of the intended one. That is a silent wrong-site write.
   *
   * @param string $siteName
   *   The site's machine name.
   *
   * @return string
   *   The fully resolved uri, for drush's --uri.
   *
   * @throws \InvalidArgumentException
   *   If the template still holds an unsubstituted token afterwards. Sending
   *   a literal ${...} to --uri would bootstrap the wrong site rather than
   *   fail, so this is always an error.
   */
  public function uriFor(string $siteName): string {
    $uri = str_replace(
      sprintf('${%s}', self::SITE_NAME_TOKEN),
      strtolower($siteName),
      $this->uriTemplate
    );

    if (preg_match(self::TOKEN_PATTERN, $uri, $matches) === 1) {
      throw new \InvalidArgumentException(sprintf(
        'Site alias uri "%s" contains an unsupported token "${%s}". Only ${%s} is substituted.',
        $this->uriTemplate,
        $matches[1],
        self::SITE_NAME_TOKEN
      ));
    }

    return $uri;
  }

  /**
   * The literal domain suffix this alias's uri template ends with.
   *
   * For "https://${env-name}.example.invalid" that is ".example.invalid".
   * Used to recover a site name from a domain when the ACSF API returned no
   * machine name.
   *
   * @return string|null
   *   The suffix, or NULL if the template does not start with the token.
   */
  public function domainSuffix(): ?string {
    $prefix = sprintf('${%s}', self::SITE_NAME_TOKEN);
    $host = (string) parse_url($this->uriTemplate, PHP_URL_HOST);

    // parse_url() does not reliably return a host for a template whose
    // authority begins with "${", so fall back to string inspection.
    if ($host === '' || !str_starts_with($host, $prefix)) {
      $start = strpos($this->uriTemplate, $prefix);
      if ($start === FALSE) {
        return NULL;
      }
      $rest = substr($this->uriTemplate, $start + strlen($prefix));
      $rest = explode('/', $rest)[0];
      return $rest === '' ? NULL : $rest;
    }

    return substr($host, strlen($prefix));
  }

  /**
   * Recovers a site machine name from a domain inside this factory.
   *
   * Deliberately narrow: it only accepts a domain already within this
   * alias's own domain space, so it can never smuggle in a custom domain
   * such as abc.example.com, which may be proxied, unattached or mid-migration.
   *
   * @param string $domain
   *   The site's domain as reported by the ACSF API.
   *
   * @return string|null
   *   The recovered machine name, or NULL if the domain is foreign.
   */
  public function siteNameFromDomain(string $domain): ?string {
    $suffix = $this->domainSuffix();
    if ($suffix === NULL) {
      return NULL;
    }

    $domain = strtolower(rtrim($domain, '.'));
    $suffix = strtolower($suffix);

    if (!str_ends_with($domain, $suffix)) {
      return NULL;
    }

    $prefix = substr($domain, 0, -strlen($suffix));
    // A bare suffix, or a deeper subdomain, is not a site name.
    if ($prefix === '' || str_contains($prefix, '.')) {
      return NULL;
    }

    return $prefix;
  }

}

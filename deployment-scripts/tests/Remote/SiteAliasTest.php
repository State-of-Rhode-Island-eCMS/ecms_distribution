<?php

declare(strict_types=1);

namespace Ecms\Deployment\Tests\Remote;

use Ecms\Deployment\Remote\SiteAlias;
use PHPUnit\Framework\TestCase;

/**
 * Fictional values only — these assertions are committed to a public repo,
 * so they must never encode real infrastructure.
 */
final class SiteAliasTest extends TestCase {

  private function alias(string $uriTemplate = 'https://${env-name}.example.invalid'): SiteAlias {
    return new SiteAlias(
      host: 'examplegroup01live.ssh.example-realm.example.invalid',
      user: 'examplegroup.01live',
      docroot: '/var/www/html/examplegroup.01live/docroot',
      drushScript: '/var/www/html/examplegroup.01live/vendor/bin/drush',
      uriTemplate: $uriTemplate,
    );
  }

  public function testUserAtHost(): void {
    $this->assertSame(
      'examplegroup.01live@examplegroup01live.ssh.example-realm.example.invalid',
      $this->alias()->userAtHost()
    );
  }

  public function testUriForSubstitutesEnvName(): void {
    $this->assertSame('https://abc.example.invalid', $this->alias()->uriFor('abc'));
  }

  public function testUriForLowercasesTheSiteName(): void {
    // ACSF site keys are not reliably lower case, and Drupal's multisite
    // lookup matches sites.php keys as plain strings — a mixed-case host
    // can silently bootstrap the default site instead.
    $this->assertSame('https://abc.example.invalid', $this->alias()->uriFor('ABC'));
  }

  public function testUriForHandlesTheDevAndTestTemplates(): void {
    $this->assertSame(
      'https://abc.test-example.invalid',
      $this->alias('https://${env-name}.test-example.invalid')->uriFor('abc')
    );
  }

  public function testUnsubstitutedTokenIsRefused(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('${db-name}');
    $this->alias('https://${env-name}.${db-name}.example.invalid')->uriFor('abc');
  }

  public function testSiteNameFallsBackToDomainPrefixWithinTheFactory(): void {
    $this->assertSame('abc', $this->alias()->siteNameFromDomain('abc.example.invalid'));
  }

  public function testSiteNameFallbackIsCaseInsensitive(): void {
    $this->assertSame('abc', $this->alias()->siteNameFromDomain('ABC.Example.Invalid'));
  }

  public function testSiteNameFallbackRejectsAForeignDomain(): void {
    // A custom domain may be proxied, unattached or mid-migration; using it
    // risks bootstrapping the wrong site.
    $this->assertNull($this->alias()->siteNameFromDomain('abc.example.com'));
  }

  public function testSiteNameFallbackRejectsABareSuffix(): void {
    $this->assertNull($this->alias()->siteNameFromDomain('example.invalid'));
  }

  public function testSiteNameFallbackRejectsADeeperSubdomain(): void {
    $this->assertNull($this->alias()->siteNameFromDomain('a.b.example.invalid'));
  }

  public function testDomainSuffixIsTheLiteralPartOfTheTemplate(): void {
    $this->assertSame('.example.invalid', $this->alias()->domainSuffix());
  }

}

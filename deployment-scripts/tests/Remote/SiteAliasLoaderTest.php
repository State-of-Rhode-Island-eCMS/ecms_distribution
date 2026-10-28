<?php

declare(strict_types=1);

namespace Ecms\Deployment\Tests\Remote;

use Ecms\Deployment\Config\FactoryEnvironment;
use Ecms\Deployment\Remote\SiteAliasLoader;
use PHPUnit\Framework\TestCase;

/**
 * Fixtures are organised one directory per scenario, because the loader
 * derives the filename from the environment — FactoryEnvironment only ever
 * yields 01dev/01test/01live, so a flat directory of files named
 * "malformed.site.yml" would be unreachable without an artificial seam.
 * Each scenario owns its own 01live.site.yml instead.
 */
final class SiteAliasLoaderTest extends TestCase {

  private function loader(string $scenario): SiteAliasLoader {
    return new SiteAliasLoader(__DIR__ . '/../fixtures/drush-sites/' . $scenario);
  }

  public function testLoadsEachEnvironmentFromItsAliasFile(): void {
    $loader = $this->loader('valid');

    $prod = $loader->load(FactoryEnvironment::Prod);
    $this->assertSame('examplegroup01live.ssh.example-realm.example.invalid', $prod->host);
    $this->assertSame('examplegroup.01live', $prod->user);
    $this->assertSame('/var/www/html/examplegroup.01live/docroot', $prod->docroot);
    $this->assertSame('/var/www/html/examplegroup.01live/vendor/bin/drush', $prod->drushScript);
    $this->assertSame('https://${env-name}.example.invalid', $prod->uriTemplate);
    $this->assertSame('examplegroup', $prod->acSite);
    $this->assertSame('example-realm', $prod->acRealm);

    $this->assertSame('https://${env-name}.dev-example.invalid', $loader->load(FactoryEnvironment::Dev)->uriTemplate);
    $this->assertSame('https://${env-name}.test-example.invalid', $loader->load(FactoryEnvironment::Test)->uriTemplate);
  }

  public function testFilenameIsDerivedFromAcquiaEnvironment(): void {
    // "prod" reads 01live.site.yml, not prod.site.yml.
    $this->assertStringEndsWith(
      '/01live.site.yml',
      $this->loader('valid')->pathFor(FactoryEnvironment::Prod)
    );
    $this->assertStringEndsWith(
      '/01test.site.yml',
      $this->loader('valid')->pathFor(FactoryEnvironment::Test)
    );
  }

  public function testMissingFileNamesThePathAndPointsAtTheDownload(): void {
    $loader = $this->loader('does-not-exist');

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('#does-not-exist/01live\.site\.yml#');
    $this->expectExceptionMessageMatches('#Acquia Cloud#');
    $loader->load(FactoryEnvironment::Prod);
  }

  public function testMalformedYamlNamesThePathAndTheParseError(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('#Could not parse site alias .*/malformed/01live\.site\.yml#');
    $this->loader('malformed')->load(FactoryEnvironment::Prod);
  }

  public function testMissingRequiredKeyNamesThePathGroupAndKey(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('#group "\*" is missing required key\(s\).*host#s');
    $this->loader('missing-keys')->load(FactoryEnvironment::Prod);
  }

  public function testMissingNestedDrushScriptIsReported(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('#paths\.drush-script#');
    $this->loader('missing-keys')->load(FactoryEnvironment::Prod);
  }

  public function testNoMatchingGroupListsTheGroupsPresent(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('#Groups present: abc, zyx#');
    $this->loader('named-groups-only')->load(FactoryEnvironment::Prod, 'nosuchsite');
  }

  public function testAcEnvMismatchIsRefused(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('#declares ac-env "01test" but the target is "01live"#');
    $this->loader('wrong-ac-env')->load(FactoryEnvironment::Prod);
  }

  public function testAssertUsableRefusesAcEnvMismatch(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('#group "\*" declares ac-env "01test" but the target is "01live"#');
    $this->loader('wrong-ac-env')->assertUsable(FactoryEnvironment::Prod);
  }

  public function testPerSiteGroupWinsOverWildcard(): void {
    $loader = $this->loader('per-site-group');

    $this->assertSame(
      'stack2-01live.ssh.example-realm.example.invalid',
      $loader->load(FactoryEnvironment::Prod, 'abc')->host
    );
    $this->assertSame(
      'examplegroup01live.ssh.example-realm.example.invalid',
      $loader->load(FactoryEnvironment::Prod, 'other')->host
    );
  }

  public function testGroupMatchingIsCaseInsensitive(): void {
    // ACSF site keys are not reliably lower case (ABC, ZYX).
    $this->assertSame(
      'stack2-01live.ssh.example-realm.example.invalid',
      $this->loader('per-site-group')->load(FactoryEnvironment::Prod, 'ABC')->host
    );
  }

  public function testAssertUsableAcceptsAFileWithNoWildcardGroup(): void {
    // Drush treats '*' as a fallback, not a requirement, so a file of named
    // site groups is valid. Requiring '*' would reject a whole factory.
    $loader = $this->loader('named-groups-only');

    $loader->assertUsable(FactoryEnvironment::Prod);
    $this->assertSame(['abc', 'zyx'], $loader->groupsIn(FactoryEnvironment::Prod));
    $this->assertSame(
      'examplegroup.01live',
      $loader->load(FactoryEnvironment::Prod, 'abc')->user
    );
  }

  public function testAssertUsableRejectsAFileWhereNoGroupHasTheRequiredKeys(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('#No usable group in site alias#');
    $this->loader('missing-keys')->assertUsable(FactoryEnvironment::Prod);
  }

  public function testAssertUsableRejectsAFileWithNoGroupsAtAll(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessageMatches('#No usable group in site alias#');
    $this->loader('no-group')->assertUsable(FactoryEnvironment::Prod);
  }

  public function testAssertUsableAcceptsTheValidFixtures(): void {
    $loader = $this->loader('valid');
    foreach (FactoryEnvironment::cases() as $environment) {
      $loader->assertUsable($environment);
    }
    $this->assertSame(['*'], $loader->groupsIn(FactoryEnvironment::Prod));
  }

}

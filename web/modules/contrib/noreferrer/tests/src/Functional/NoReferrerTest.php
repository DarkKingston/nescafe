<?php

namespace Drupal\Tests\noreferrer\Functional;

use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the No Referrer module.
 *
 * @group No Referrer
 */
class NoReferrerTest extends BrowserTestBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Modules to enable.
   *
   * @var string[]
   */
  protected static $modules = ['noreferrer', 'help'];

  /**
   * Functional tests for the rel="noreferrer" attribute.
   */
  public function testNoReferrer(): void {
    $admin_user = $this->drupalCreateUser([
      'administer site configuration',
      'access administration pages',
    ]);
    $this->drupalLogin($admin_user);
    $edit = ['allowed_domains' => 'drupal.org example.org', 'publish' => 1];
    $this->drupalGet('admin/config/content/noreferrer');
    $this->submitForm($edit, $this->t('Save configuration'));
    $this->assertSame('<a href="https://example.com/" rel="noreferrer">test</a>', (string) Link::fromTextAndUrl('test', Url::fromUri('https://example.com/'))->toString());
    $this->assertSame('<a href="https://drupal.org/">test</a>', (string) Link::fromTextAndUrl('test', Url::fromUri('https://drupal.org/'))->toString());
    $this->assertSame('<a href="https://drupal.org/" target="_blank" rel="noopener">test</a>', (string) Link::fromTextAndUrl('test', Url::fromUri('https://drupal.org/', ['attributes' => ['target' => '_blank']]))->toString());
    $this->assertSame('<a href="https://DRUPAL.ORG/">test</a>', (string) Link::fromTextAndUrl('test', Url::fromUri('https://DRUPAL.ORG/'))->toString());
    $this->assertSame('<a href="https://api.drupal.org/">test</a>', (string) Link::fromTextAndUrl('test', Url::fromUri('https://api.drupal.org/'))->toString());
    $this->assertSame('<a href="https://example.com/" target="_new_tab" rel="noopener noreferrer">test</a>', (string) Link::fromTextAndUrl('test', Url::fromUri('https://example.com/', ['attributes' => ['target' => '_new_tab']]))->toString());
    $this->assertSame('<a href="https://example.org/" target="0" rel="noopener">test</a>', (string) Link::fromTextAndUrl('test', Url::fromUri('https://example.org/', ['attributes' => ['target' => '0']]))->toString());
    // Test help page.
    $this->drupalGet('admin/help/noreferrer');
  }

}

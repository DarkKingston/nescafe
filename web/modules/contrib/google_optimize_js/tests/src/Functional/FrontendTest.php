<?php

namespace Drupal\Tests\google_optimize_js\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * A test that ensures that the JS snippets are conditionally rendered.
 *
 * @group google_optimize_js
 */
class FrontendTest extends BrowserTestBase {

  /**
   * {@inheritDoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritDoc}
   */
  protected static $modules = [
    'google_optimize_js',
  ];

  /**
   * Tests that synchronous javascript is rendered on the front end.
   */
  public function testSynchronousStrategy() {

    // No scripts should render without being configured.
    $this->drupalGet(Url::fromRoute('<front>'));
    $page_content = $this->getSession()->getPage()->getContent();

    $this->assertStringNotContainsString('https://www.googleoptimize.com/optimize.js?id=GTM-XXXXXXX', $page_content);
    $this->assertStringNotContainsString('a,s,y,n,c,h,i,d,e', $page_content);

    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $this->container->get('config.factory');

    // Simulate if form validation has been defeated (ie by direct sql query).
    $config_factory->getEditable('google_optimize_js.settings')
      ->set('container', '"><script>alert("XSS!");</script>')
      ->set('enabled', TRUE)
      ->set('anti_flicker_pages', '<front>')
      ->save();

    $this->drupalGet(Url::fromRoute('<front>'));
    $page_content = $this->getSession()->getPage()->getContent();

    $this->assertStringNotContainsString('https://www.googleoptimize.com/optimize.js', $page_content);
    $this->assertStringNotContainsString('a,s,y,n,c,h,i,d,e', $page_content);

    // Simulate correct setup.
    $config_factory->getEditable('google_optimize_js.settings')
      ->set('container', 'GTM-XXXXXXX')
      ->save();

    // Scripts should render after being configured to.
    $this->drupalGet(Url::fromRoute('<front>'));
    $page_content = $this->getSession()->getPage()->getContent();

    $this->assertStringContainsString('https://www.googleoptimize.com/optimize.js?id=GTM-XXXXXXX', $page_content);
    $this->assertStringContainsString('a,s,y,n,c,h,i,d,e', $page_content);
  }

  /**
   * Tests that asynchronous javascript is rendered on the front end.
   */
  public function testAsynchronousStrategy() {

    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $this->container->get('config.factory');

    $config_factory->getEditable('google_optimize_js.settings')
      ->set('container', 'GTM-XXXXXXX')
      ->set('enabled', TRUE)
      ->set('anti_flicker_pages', '<front>')
      ->set('loading_strategy', 'asynchronous')
      ->save();

    $this->drupalGet(Url::fromRoute('<front>'));
    $page_content = $this->getSession()->getPage()->getContent();

    $this->assertStringContainsString('<script src="https://www.googleoptimize.com/optimize.js?id=GTM-XXXXXXX" async', $page_content);
  }

}

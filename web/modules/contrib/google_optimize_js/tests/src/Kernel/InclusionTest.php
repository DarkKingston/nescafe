<?php

namespace Drupal\Tests\google_optimize_js\Kernel;

use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Routing\AdminContext;
use Drupal\KernelTests\KernelTestBase;

/**
 * A test the confirms the proper operation of the inclusion service.
 *
 * @group google_optimize_js
 */
class InclusionTest extends KernelTestBase {

  /**
   * {@inheritDoc}
   */
  public static $modules = [
    'path_alias',
    'google_optimize_js',
  ];

  /**
   * The subject under test.
   *
   * @var \Drupal\google_optimize_js\InclusionInterface
   */
  protected $instance;

  /**
   * The optimize configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The mocked current path stack service.
   *
   * @var \Drupal\Core\Path\CurrentPathStack|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currentPathStack;

  /**
   * The mocked admin context service.
   *
   * @var \Drupal\Core\Routing\AdminContext|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $adminContext;

  /**
   * {@inheritDoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installConfig('google_optimize_js');

    $this->currentPathStack = $this->getMockBuilder(CurrentPathStack::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->container->set('path.current', $this->currentPathStack);

    $this->adminContext = $this->getMockBuilder(AdminContext::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->container->set('router.admin_context', $this->adminContext);

    $this->instance = $this->container->get('google_optimize_js.inclusion');

    /* @var $config_factory \Drupal\Core\Config\ConfigFactoryInterface */
    $config_factory = $this->container->get('config.factory');

    $this->config = $config_factory->getEditable('google_optimize_js.settings');
  }

  /**
   * If no container is defined, optimize should never be added.
   */
  public function testNoContainerDefined() {
    $this->config
      ->set('container', '')
      ->set('enabled', TRUE)
      ->save();

    $this->assertFalse($this->instance->includeOptimizeSnippet());
    $this->assertFalse($this->instance->includeAntiFlickerSnippet());
  }

  /**
   * If disabled, optimize should never be added.
   */
  public function testNotEnabled() {
    $this->config
      ->set('container', 'GTM-XXXXX')
      ->set('enabled', FALSE)
      ->save();

    $this->assertFalse($this->instance->includeOptimizeSnippet());
    $this->assertFalse($this->instance->includeAntiFlickerSnippet());
  }

  /**
   * If the page is an admin page, optimize should never be added.
   */
  public function testAdminPath() {
    $this->config
      ->set('container', 'GTM-XXXXX')
      ->set('enabled', TRUE)
      ->save();

    $this->adminContext
      ->method('isAdminRoute')
      ->willReturn(TRUE);

    $this->assertFalse($this->instance->includeOptimizeSnippet());
    $this->assertFalse($this->instance->includeAntiFlickerSnippet());
  }

  /**
   * If the pages to include are blank, include optimize everywhere.
   */
  public function testNoAntiFlicker() {
    $this->config
      ->set('container', 'GTM-XXXXX')
      ->set('enabled', TRUE)
      ->save();

    $this->assertTrue($this->instance->includeOptimizeSnippet());
    $this->assertFalse($this->instance->includeAntiFlickerSnippet());

    $this->currentPathStack
      ->method('getPath')
      ->willReturn('/anywhere');

    $this->assertTrue($this->instance->includeOptimizeSnippet());
    $this->assertFalse($this->instance->includeAntiFlickerSnippet());
  }

  /**
   * Test case for when anti-flicker is set up to appear on disabled paths.
   */
  public function testMisconfiguredAntiFlicker() {
    $this->currentPathStack->method('getPath')
      ->willReturn('/test-path');

    // Set up optimize to not load on /test-path, but load anti-flicker anyway.
    $this->config
      ->set('container', 'GTM-XXXXX')
      ->set('enabled', TRUE)
      ->set('pages', '/test-path-2')
      ->set('anti_flicker_pages', '/test-path')
      ->save();

    $this->assertFalse($this->instance->includeOptimizeSnippet());
    $this->assertFalse($this->instance->includeAntiFlickerSnippet());
  }

  /**
   * If pages to include are filled, then conditionally include optimize.
   */
  public function testNotIncludedOnPage() {
    $this->config
      ->set('container', 'GTM-XXXXX')
      ->set('enabled', TRUE)
      ->set('pages', '/test-path')
      ->save();

    $this->currentPathStack->method('getPath')
      ->willReturn('/not-on-the-list', '/test-path');

    $this->assertFalse($this->instance->includeOptimizeSnippet());
    $this->assertFalse($this->instance->includeAntiFlickerSnippet());

    $this->assertTrue($this->instance->includeOptimizeSnippet());
    $this->assertFalse($this->instance->includeAntiFlickerSnippet());
  }

  /**
   * Test case for an exact path match.
   */
  public function testIncludedOnPage() {
    $this->currentPathStack->method('getPath')
      ->willReturn('/test-path');

    $this->config
      ->set('container', 'GTM-XXXXX')
      ->set('enabled', TRUE)
      ->set('pages', '/test-path')
      ->set('anti_flicker_pages', '/test-path')
      ->save();

    $this->assertTrue($this->instance->includeOptimizeSnippet());
    $this->assertTrue($this->instance->includeAntiFlickerSnippet());
  }

  /**
   * Test case for a wildcard path match.
   */
  public function testWildcard() {
    $this->currentPathStack->method('getPath')
      ->willReturn('/test-path-12345');

    $this->config
      ->set('container', 'GTM-XXXXX')
      ->set('enabled', TRUE)
      ->set('pages', '/test-path-*')
      ->set('anti_flicker_pages', '/test-path-*')
      ->save();

    $this->assertTrue($this->instance->includeOptimizeSnippet());
    $this->assertTrue($this->instance->includeAntiFlickerSnippet());
  }

}

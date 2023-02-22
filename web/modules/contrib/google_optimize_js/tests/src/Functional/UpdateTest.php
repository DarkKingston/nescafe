<?php

namespace Drupal\Tests\google_optimize_js\Functional;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Test cases for upgrade paths.
 *
 * @group google_optimize_js
 */
class UpdateTest extends UpdatePathTestBase {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->configFactory = $this->container->get('config.factory');
  }

  /**
   * Gets an uncached copy of the module config.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   The module config.
   */
  protected function getModuleConfig() {
    $this->configFactory->clearStaticCache();
    return $this->configFactory->get('google_optimize_js.settings');
  }

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      static::getDrupalRoot() . '/core/modules/system/tests/fixtures/update/drupal-9.0.0.bare.standard.php.gz',
      __DIR__ . '/../../fixtures/google_optimize_js.update-hook-test.php',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function doSelectionTest() {
    parent::doSelectionTest();
    $this->assertSession()->responseContains('Sets the proper default configuration for loading_strategy.');
  }

  /**
   * Test case for google_optimize_js_post_update_loading_strategy().
   */
  public function testPostUpdateLoadingStrategy() {
    static::assertNull($this->getModuleConfig()->get('loading_strategy'));
    $this->runUpdates();
    static::assertEquals('synchronous', $this->getModuleConfig()->get('loading_strategy'));
  }

}

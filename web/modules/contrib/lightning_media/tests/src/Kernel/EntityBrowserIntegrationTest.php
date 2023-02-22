<?php

namespace Drupal\Tests\lightning_media\Kernel;

use Drupal\entity_browser\Entity\EntityBrowser;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests Lightning Media's integration with Entity Browser.
 *
 * @group lightning_media
 */
class EntityBrowserIntegrationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'entity_browser',
    'entity_browser_example',
    'field',
    'file',
    'image',
    'lightning_media',
    'media',
    'node',
    'system',
    'text',
    'user',
    'views',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installSchema('system', 'key_value_expire');
    // These are two separate calls because the config must be installed in this
    // specific order for the test to pass.
    $this->installConfig('node');
    $this->installConfig('entity_browser_example');
  }

  /**
   * Tests that our libraries are not attached to custom entity browsers.
   */
  public function testLibraryAttachment(): void {
    /** @var \Drupal\entity_browser\EntityBrowserInterface $browser */
    $browser = EntityBrowser::load('test_files');
    $this->assertInstanceOf(EntityBrowser::class, $browser);

    $form = $this->container->get('form_builder')
      ->getForm($browser->getFormObject());
    $this->assertIsArray($form);
    $this->assertNotContains('lightning_media/browser.styling', $form['#attached']['library']);
  }

}

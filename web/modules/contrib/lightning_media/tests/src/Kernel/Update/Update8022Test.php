<?php

namespace Drupal\Tests\lightning_media\Kernel\Update;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests Lightning Media's 8022 update hook.
 *
 * @group lightning_media
 */
class Update8022Test extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'lightning_media',
    'media',
    'system',
  ];

  /**
   * Tests Lightning Media's 8022 update hook.
   */
  public function testUpdate() {
    $setting = $this->config('lightning_media.settings')->get('revision_ui');
    $this->assertNull($setting);

    $this->container->get('module_handler')
      ->loadInclude('lightning_media', 'install');
    lightning_media_update_8022();

    $setting = $this->config('lightning_media.settings')->get('revision_ui');
    $this->assertTrue($setting);
  }

}

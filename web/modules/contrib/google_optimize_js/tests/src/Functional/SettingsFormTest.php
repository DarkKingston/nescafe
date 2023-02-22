<?php

namespace Drupal\Tests\google_optimize_js\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * A test that confirms the proper operation of the settings form.
 *
 * @group google_optimize_js
 */
class SettingsFormTest extends BrowserTestBase {

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
   * Tests that users without permission are unable to edit settings.
   */
  public function testNoPermission() {

    // Ensure that anonymous users are denied access.
    $this->drupalGet('/admin/config/system/google_optimize');
    $this->assertResponse(403);

    // Ensure that authenticated users without permission are denied access.
    $this->drupalLogin($this->drupalCreateUser());
    $this->drupalGet('/admin/config/system/google_optimize');
    $this->assertResponse(403);
  }

  /**
   * Tests that users with permission are able to edit settings.
   */
  public function testPermission() {
    $this->drupalLogin($this->drupalCreateUser(['administer google optimize']));

    // Ensure that authorized users are allowed access.
    $this->drupalGet('/admin/config/system/google_optimize');
    $this->assertResponse(200);

    $edit = [
      'container' => '"</script><script>alert("XSS!");</script>',
      'enabled' => TRUE,
      'pages' => '/test-page-*',
      'anti_flicker_pages' => '/test-page',
      'anti_flicker_timeout' => 5000,
      'datalayer_js_weight' => 1,
    ];

    $this->drupalPostForm(NULL, $edit, 'Save configuration');
    $this->assertText('Container Id field is not in the right format.');

    // Fill out the form.
    $edit = [
      'container' => 'GTM-XXXXXXX',
      'enabled' => TRUE,
      'pages' => '/test-page-*',
      'anti_flicker_pages' => '/test-page',
      'anti_flicker_timeout' => 5000,
      'datalayer_js_weight' => 1,
      'loading_strategy' => 'asynchronous',
    ];

    $this->drupalPostForm(NULL, $edit, 'Save configuration');

    $config = $this->config('google_optimize_js.settings');
    foreach ($edit as $k => $v) {

      // Assert the value on the form has updated.
      $form_element = $this->getSession()->getPage()->findField($k);
      $this->assertEqual($form_element->getValue(), $v);

      // Assert that configuration has updated.
      $this->assertEqual($config->get($k), $v);
    }
  }

}

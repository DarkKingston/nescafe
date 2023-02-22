<?php

namespace Drupal\Tests\paragraphs_sets\Functional;

use Drupal\Tests\system\Functional\Module\ModuleTestBase;

/**
 * Test that paragraphs_sets_requirements() does not interrupt the install flow.
 *
 * Note testbot on drupal.org is not sophisticated enough for us to test what
 * happens if paragraphs is not present or is an old version which is
 * incompatible with paragraphs_sets... we can only test this one case.
 *
 * @group paragraphs_sets
 */
class ModuleInstallTest extends ModuleTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   *
   * Note this is empty so testModuleInstalls() validates what happens if we try
   * to install paragraphs_tests without its dependencies being installed first.
   */
  public static $modules = [];

  /**
   * Test that paragraphs_sets_requirements() doesn't interrupt install flow.
   */
  public function testModuleInstalls() {
    // Get the "Extend" page, check "Paragraphs Sets",
    // Note that \Drupal\Tests\system\Functional\Module\ModuleTestBase::setUp()
    // logs us in with a user that can install/uninstall modules.
    $this->drupalGet('admin/modules');
    $modulesPage = $this->getSession()->getPage();
    $modulesPage->checkField('modules[paragraphs_sets][enable]');
    $modulesPage->pressButton('Install');

    // Test we get a "Some required modules must be enabled" page, and not an
    // error.
    $this->assertSession()->statusCodeNotEquals(500);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Some required modules must be enabled');
    $this->assertSession()->pageTextContains('You must enable the');
    $this->assertSession()->pageTextContains('modules to install Paragraphs Sets');
  }

}

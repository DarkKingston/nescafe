<?php

namespace Drupal\webform_iban_field\Tests;

use Drupal\Tests\webform\Functional\Element\WebformElementBrowserTestBase;
use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;
/**
 * Tests for webform_iban_field.
 *
 * @group Webform
 */
class WebformIbanFieldTest extends WebformElementBrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['webform_iban_field'];

  /**
   * Tests IBAN field.
   */
  public function testWebformIbanField() {
    $webform = Webform::load('webform_iban_field');

    // Check form element rendering.
    $this->drupalGet('webform/webform_iban_field');

    $this->assertFieldByName('webform_iban_field');
    $this->assertFieldByName('webform_iban_field_multiple[items][0][_item_]');
    $this->assertElementPresent('.form-text.webform-iban-field');

    // Check webform element submission.

    // Submission fail.
    $edit = [
      'webform_iban_field' => '{Test}',
      'webform_iban_field_multiple[items][0][_item_]' => '{Test 01}',
    ];
    $sid = $this->postSubmission($webform, $edit);
    $this->assertEqual($sid, NULL);

    // Submission succeed.
    $edit = [
      'webform_iban_field' => 'NL78LPLN0822253585',
      'webform_iban_field_multiple[items][0][_item_]' => 'NL52BNGH0374594309',
    ];
    $sid = $this->postSubmission($webform, $edit);
    $webform_submission = WebformSubmission::load($sid);
    $this->assertEqual($webform_submission->getElementData('webform_iban_field'), 'NL78LPLN0822253585');
    $this->assertEqual($webform_submission->getElementData('webform_iban_field_multiple'), ['NL52BNGH0374594309']);
  }

}

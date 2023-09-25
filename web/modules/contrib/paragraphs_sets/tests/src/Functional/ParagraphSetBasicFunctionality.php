<?php

namespace Drupal\Tests\paragraphs_sets\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\paragraphs_sets\Traits\ParagraphsSetsFunctionalTestTrait;

/**
 * Tests the basic functionality of Paragraphs Sets.
 *
 * Note that we verify using simple paragraphs containing a text field.
 *
 * Note that variables used in this test are prefixed with "sut", i.e.: "System
 * Under Test", so they don't conflict with variables defined in parent classes
 * or traits.
 *
 * @group paragraphs_sets
 */
class ParagraphSetBasicFunctionality extends BrowserTestBase {
  use ParagraphsSetsFunctionalTestTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public static $modules = ['node', 'paragraphs_sets'];

  /**
   * The machine name of the paragraph reference field in the node type.
   *
   * @var string
   */
  protected $sutNodeParagraphField;

  /**
   * The machine name of the node type.
   *
   * @var string
   */
  protected $sutNodeType;

  /**
   * The machine name of the first paragraph set.
   *
   * @var string
   */
  protected $sutParagraphSetFirst;

  /**
   * The machine name of the second paragraph set.
   *
   * @var string
   */
  protected $sutParagraphSetSecond;

  /**
   * The machine name of the paragraph type.
   *
   * @var string
   */
  protected $sutParagraphType;

  /**
   * The machine name of the text field in the paragraph type.
   *
   * @var string
   */
  protected $sutParagraphTextField;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create a paragraph. Add a text field to that paragraph.
    $this->sutParagraphType = mb_strtolower($this->randomMachineName());
    $this->addParagraphsType($this->sutParagraphType);
    $this->sutParagraphTextField = mb_strtolower($this->randomMachineName());
    $this->addTextFieldInParagraphType($this->sutParagraphTextField, $this->sutParagraphType);

    // Add a first paragraph set.
    $this->sutParagraphSetFirst = $this->randomMachineName();
    $configFirst = <<<EOF
paragraphs:
  - type: {$this->sutParagraphType}
    data:
      {$this->sutParagraphTextField}: One
  - type: {$this->sutParagraphType}
    data:
      {$this->sutParagraphTextField}: Two
  - type: {$this->sutParagraphType}
    data:
      {$this->sutParagraphTextField}: Three
EOF;
    $this->addParagraphSet($this->sutParagraphSetFirst, $configFirst);

    // Add a second paragraph set.
    $this->sutParagraphSetSecond = $this->randomMachineName();
    $configSecond = <<<EOF
paragraphs:
  - type: {$this->sutParagraphType}
    data:
      {$this->sutParagraphTextField}: Four
  - type: {$this->sutParagraphType}
    data:
      {$this->sutParagraphTextField}: Five
EOF;
    $this->addParagraphSet($this->sutParagraphSetSecond, $configSecond);

    // Add a node. Add a paragraph reference field to that node.
    $this->sutNodeType = mb_strtolower($this->randomMachineName());
    $this->addNodeType($this->sutNodeType);
    $this->sutNodeParagraphField = mb_strtolower($this->randomMachineName());
    $this->addParagraphRefFieldInNodeType($this->sutNodeParagraphField, $this->sutNodeType);
  }

  /**
   * Test Paragraph Sets basic functionality.
   */
  public function testParagraphSetsWithTextFields() {
    // Load the node/add page.
    $this->drupalLogin($this->drupalCreateUser([
      'administer nodes',
      "create {$this->sutNodeType} content",
    ]));
    $this->drupalGet(Url::fromRoute('node.add', [
      'node_type' => $this->sutNodeType,
    ])->toString());
    $this->assertSession()->statusCodeEquals(200);
    $page = $this->getSession()->getPage();

    // Check that the paragraphs sets controls are present.
    $page->hasSelect('Paragraph set');
    $page->hasButton('Select set');
    $this->assertSession()->pageTextContains(sprintf('for %s', $this->sutNodeParagraphField));
    $page->hasButton('Append set');
    $this->assertSession()->pageTextContains(sprintf('for %s', $this->sutNodeParagraphField));

    $textFieldSelectorSuffix = "[subform][{$this->sutParagraphTextField}][0][value]";

    // Select a paragraph set.
    $page->selectFieldOption('Paragraph set', $this->sutParagraphSetFirst, FALSE);
    $page->pressButton('Select set');

    // Assert there are now 3 paragraph fields with the data pre-populated.
    $this->assertSession()->elementsCount('css', sprintf('[data-paragraphs-bundle="%s"]', $this->sutParagraphType), 3);
    $this->assertSession()->fieldValueEquals(sprintf('%s[0]%s', $this->sutNodeParagraphField, $textFieldSelectorSuffix), 'One');
    $this->assertSession()->fieldValueEquals(sprintf('%s[1]%s', $this->sutNodeParagraphField, $textFieldSelectorSuffix), 'Two');
    $this->assertSession()->fieldValueEquals(sprintf('%s[2]%s', $this->sutNodeParagraphField, $textFieldSelectorSuffix), 'Three');

    // Append a paragraph set.
    $page->selectFieldOption('Paragraph set', $this->sutParagraphSetSecond, FALSE);
    $page->pressButton('Append set');

    // Assert there are 6 paragraph fields with the data pre-populated.
    $this->assertSession()->elementsCount('css', sprintf('[data-paragraphs-bundle="%s"]', $this->sutParagraphType), 5);
    $this->assertSession()->fieldValueEquals(sprintf('%s[0]%s', $this->sutNodeParagraphField, $textFieldSelectorSuffix), 'One');
    $this->assertSession()->fieldValueEquals(sprintf('%s[1]%s', $this->sutNodeParagraphField, $textFieldSelectorSuffix), 'Two');
    $this->assertSession()->fieldValueEquals(sprintf('%s[2]%s', $this->sutNodeParagraphField, $textFieldSelectorSuffix), 'Three');
    $this->assertSession()->fieldValueEquals(sprintf('%s[3]%s', $this->sutNodeParagraphField, $textFieldSelectorSuffix), 'Four');
    $this->assertSession()->fieldValueEquals(sprintf('%s[4]%s', $this->sutNodeParagraphField, $textFieldSelectorSuffix), 'Five');
  }

}

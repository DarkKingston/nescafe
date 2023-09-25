<?php

namespace Drupal\Tests\paragraphs_sets\Traits;

use Drupal\Core\Serialization\Yaml;
use Drupal\paragraphs_sets\Entity\ParagraphsSet;
use Drupal\Tests\paragraphs\FunctionalJavascript\ParagraphsTestBaseTrait;

/**
 * Contains functions common to functional paragraphs sets tests.
 */
trait ParagraphsSetsFunctionalTestTrait {
  use ParagraphsTestBaseTrait;

  /**
   * Create a node entity bundle (content type).
   *
   * @param string $nodeType
   *   The machine name for the node type.
   */
  public function addNodeType(string $nodeType) {
    $this->drupalCreateContentType(['type' => $nodeType, 'name' => $nodeType]);
  }

  /**
   * Create a paragraph reference field in a given node bundle.
   *
   * @param string $paragraphRefFieldName
   *   The machine name for the paragraph reference field.
   * @param string $nodeType
   *   The machine name for the node type.
   */
  public function addParagraphRefFieldInNodeType(string $paragraphRefFieldName, string $nodeType) {
    $this->addParagraphsField($nodeType, $paragraphRefFieldName, 'node', 'paragraphs');

    /** @var \Drupal\Core\Entity\Entity\EntityFormDisplay $formDisplay */
    $formDisplay = \Drupal::service('entity_display.repository')->getFormDisplay('node', $nodeType);
    $displaySettings = $formDisplay->getComponent($paragraphRefFieldName);
    $displaySettings['third_party_settings']['paragraphs_sets']['paragraphs_sets']['use_paragraphs_sets'] = 1;
    $formDisplay->setComponent($paragraphRefFieldName, $displaySettings);
    $formDisplay->save();
  }

  /**
   * Create a text field in a given paragraph bundle.
   *
   * @param string $textFieldName
   *   The machine name for the text field.
   * @param string $paragraphType
   *   The machine name for the paragraph type.
   */
  public function addTextFieldInParagraphType(string $textFieldName, string $paragraphType) {
    $this->addFieldtoParagraphType($paragraphType, $textFieldName, 'string', []);
  }

  /**
   * Create a paragraphs set.
   *
   * @param string $paragraphSet
   *   The machine name for the new paragraphs set.
   * @param string $config
   *   Configuration for the new paragraphs set. Defaults to an empty set.
   */
  public function addParagraphSet(string $paragraphSet, string $config = 'paragraphs: []') {
    $decodedConfig = Yaml::decode($config);
    $paragraphsSet = ParagraphsSet::create([
      'id' => $paragraphSet,
      'label' => $paragraphSet,
      'description' => $paragraphSet,
      'paragraphs' => $decodedConfig['paragraphs'],
    ]);
    $paragraphsSet->save();
  }

}

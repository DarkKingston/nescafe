<?php

namespace Drupal\ln_quiz\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\StringItem;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Provides a field type of ln_Quiz_Answers.
 *
 * @FieldType(
 *   id = "ln_quiz_answers",
 *   label = @Translation("Quiz Answer"),
 *   default_formatter = "ln_quiz_answers_formatter",
 *   default_widget = "ln_quiz_answers_widget",
 * )
 */
class QuizAnswers extends StringItem {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);
    $properties['right'] = DataDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Is right'))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);
    $schema['columns']['right'] = [
      'type' => 'int',
      'size' => 'tiny',
      'unsigned' => TRUE,
    ];
    $schema['indexes']['right'] = ['right'];

    return $schema;
  }
}

<?php

namespace Drupal\ln_srh\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\Exception\MissingDataException;

/**
 * Provides a field type of SRHServing.
 *
 * @FieldType(
 *   id = "srh_serving",
 *   label = @Translation("SRHServing"),
 *   default_formatter = "srh_serving_formatter",
 *   default_widget = "srh_serving_widget",
 * )
 */
class SRHServing extends FieldItemBase{

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = [];
    $properties['number'] = DataDefinition::create('integer')
      ->setLabel(t('Number'))
      ->setRequired(TRUE);
    $properties['display_name'] = DataDefinition::create('string')
      ->setLabel(t('Display Name'));

    return $properties;

  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'number' => [
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ],
        'display_name' => [
          'type' => 'varchar',
          'length' => 255
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('number')->getValue();
    return $value === NULL || $value === 0;
  }

  /**
   * @return false|int
   */
  public function getNumber(){
    try {
      return $this->get('number')->getValue();
    } catch (MissingDataException $e) {
      return FALSE;
    }
  }

  /**
   * @return false|string
   */
  public function getDisplayName(){
    try {
      return $this->get('display_name')->getValue();
    } catch (MissingDataException $e) {
      return FALSE;
    }
  }

}

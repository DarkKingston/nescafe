<?php

namespace Drupal\ln_srh\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Provides a field type of SRHQuantity.
 *
 * @FieldType(
 *   id = "srh_quantity",
 *   label = @Translation("SRHQuantity"),
 *   default_formatter = "srh_quantity_formatter",
 *   default_widget = "srh_quantity_widget",
 * )
 */
class SRHQuantity extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = [];

    $properties['quantity'] = DataDefinition::create('float')
      ->setLabel(t('Quantity'));
    $properties['display'] = DataDefinition::create('float')
      ->setLabel(t('Display'));
    $properties['grams'] = DataDefinition::create('float')
      ->setLabel(t('Grams'));
    $properties['fraction'] = DataDefinition::create('string')
      ->setLabel(t('Fraction'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'quantity' => [
          'type' => 'float',
          'unsigned' => TRUE,
          'not-null' => TRUE,
        ],
        'display' => [
          'type' => 'float',
          'unsigned' => TRUE,
        ],
        'grams' => [
          'type' => 'float',
          'unsigned' => TRUE,
        ],
        'fraction' => [
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
    $value = $this->get('quantity')->getValue();
    return $value === NULL || $value === 0;
  }


}

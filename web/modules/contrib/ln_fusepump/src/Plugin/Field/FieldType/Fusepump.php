<?php

namespace Drupal\ln_fusepump\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'Fusepump' field type.
 *
 * @FieldType(
 *   id = "field_fusepump",
 *   label = @Translation("WunderMan BuyNow"),
 *   module = "ln_fusepump",
 *   category = @Translation("Lightnest"),
 *   description = @Translation("Introduce the lightbox id to display a
 *   Fusepump Buynow button."),
 *   default_widget = "fusepump_widget",
 *   default_formatter ="fusepump_formatter"
 * )
 */
class Fusepump extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type'     => 'text',
          'size'     => 'tiny',
          'not null' => FALSE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Fusepump button'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL || $value === '';
  }

}

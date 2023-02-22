<?php

namespace Drupal\ln_c_power_reviews\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'field_ln_c_power_rev_type' field type.
 *
 * @FieldType(
 *   id = "field_ln_c_power_rev_type",
 *   label = @Translation("Power Reviews"),
 *   module = "ln_c_power_reviews",
 *   category = @Translation("Lightnest"),
 *   description = @Translation("Power Reviews For PDP Pages."),
 *   default_widget = "field_ln_c_power_rev_widget",
 *   default_formatter = "field_ln_c_power_rev_formatter"
 * )
 */
class LightnestPowerReviewsType extends FieldItemBase {

  /**
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $field_definition
   *
   * @return array
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'ln_c_power_reviews_page_id' => [
          'description' => 'Power Reviews Page ID.',
          'type'        => 'text',
        ],
      ],
    ];

  }

  /**
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $field_definition
   *
   * @return array
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['ln_c_power_reviews_page_id'] = DataDefinition::create('string')
      ->setLabel(t('Power Reviews Page ID'));

    return $properties;
  }

}

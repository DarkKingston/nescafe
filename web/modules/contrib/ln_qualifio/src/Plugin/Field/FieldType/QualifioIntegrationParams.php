<?php

namespace Drupal\ln_qualifio\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'QualifioIntegrationParams' field type.
 *
 * @FieldType(
 *   id = "QualifioIntegrationParams",
 *   label = @Translation("Qualifio"),
 *   module = "ln_qualifio",
 *   category = @Translation("Lightnest"),
 *   description = @Translation("Adds an Qualifio service to a web page."),
 *   default_widget = "QualifioIntegrationWidget",
 *   default_formatter = "QualifioIntegrationFormatter"
 * )
 */
class QualifioIntegrationParams extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {

    return [
      'columns' => [
        'qualifiointegrationType'  => [
          'description' => 'Select the Campaign',
          'type'        => 'text',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['qualifiointegrationType'] = DataDefinition::create('string')
      ->setLabel(t('QualifiointegrationType'));
    return $properties;
  }

}

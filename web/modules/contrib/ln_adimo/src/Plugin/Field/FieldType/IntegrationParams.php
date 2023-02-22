<?php

namespace Drupal\ln_adimo\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'integrationParams' field type.
 *
 * @FieldType(
 *   id = "integrationParams",
 *   label = @Translation("Adimo BuyNow"),
 *   module = "ln_adimo",
 *   category = @Translation("Lightnest"),
 *   description = @Translation("Adds an Adimo web service to a web page."),
 *   default_widget = "integrationWidget",
 *   default_formatter = "integrationFormatter"
 * )
 */
class IntegrationParams extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {

    return [
      'columns' => [
        'integrationType'  => [
          'description' => 'Select the  Adimo integration type required',
          'type'        => 'text',
        ],
        'touchpointID'     => [
          'description' => 'Touchpoint ID for this control.',
          'type'        => 'text',
        ],
        'customCSS'        => [
          'description' => 'Any custom CSS that is needed.',
          'type'        => 'text',
        ],
        'customButtonHTML' => [
          'description' => 'Custom button HTML if required for enhanced recipe touchpoint',
          'type'        => 'text',
        ],
      ],
    ];
  }


  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['integrationType'] = DataDefinition::create('string')
      ->setLabel(t('integrationType'));

    $properties['touchpointID'] = DataDefinition::create('string')
      ->setLabel(t('touchpointID'));

    $properties['customCSS'] = DataDefinition::create('string')
      ->setLabel(t('customCSS'));

    $properties['customButtonHTML'] = DataDefinition::create('string')
      ->setLabel(t('customButtonHTML'));

    return $properties;
  }

}

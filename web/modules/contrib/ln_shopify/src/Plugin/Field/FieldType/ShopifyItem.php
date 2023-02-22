<?php

namespace Drupal\ln_shopify\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\text\Plugin\Field\FieldType\TextItemBase;

/**
 * Plugin implementation of the 'field_shopify' field type.
 *
 * @FieldType(
 *   id = "field_shopify",
 *   label = @Translation("Shopify"),
 *   category = @Translation("Lightnest"),
 *   default_widget = "shopify_widget",
 *   default_formatter = "shopify_formatter"
 * )
 */
class ShopifyItem extends TextItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'max_length' => 5000,
    ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'varchar',
          'length' => $field_definition->getSetting('max_length'),
        ],
        'format' => [
          'type' => 'varchar',
          'length' => 5000,
        ],
      ],
      'indexes' => [
        'format' => ['format'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();
    $constraints = parent::getConstraints();

    if ($max_length = $this->getSetting('max_length')) {
      $constraints[] = $constraint_manager->create('ComplexData', [
        'value' => [
          'Length' => [
            'max' => $max_length,
            'maxMessage' => t('%name: the text may not be longer than @max characters.', ['%name' => $this->getFieldDefinition()->getLabel(), '@max' => $max_length]),
          ],
        ],
      ]);
    }

    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element = [];

    $element['max_length'] = [
      '#type' => 'number',
      '#title' => t('Maximum length'),
      '#default_value' => $this->getSetting('max_length'),
      '#required' => TRUE,
      '#description' => t('The maximum length of the field in characters.'),
      '#min' => 1,
      '#disabled' => $has_data,
    ];
    $element += parent::storageSettingsForm($form, $form_state, $has_data);

    return $element;
  }

}

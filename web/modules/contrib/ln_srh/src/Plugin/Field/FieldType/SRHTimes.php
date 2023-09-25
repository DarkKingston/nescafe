<?php

namespace Drupal\ln_srh\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Provides a field type of SRHTimes.
 *
 * @FieldType(
 *   id = "srh_times",
 *   label = @Translation("SRHTimes"),
 *   default_formatter = "srh_times_formatter",
 *   default_widget = "srh_times_widget",
 * )
 */
class SRHTimes extends FieldItemBase{

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = [];

    $properties['total'] = DataDefinition::create('integer')
      ->setLabel(t('Total'));
    $properties['serving'] = DataDefinition::create('integer')
      ->setLabel(t('Serving'));
    $properties['preparation'] = DataDefinition::create('integer')
      ->setLabel(t('Preparation'));
    $properties['cooking'] = DataDefinition::create('integer')
      ->setLabel(t('Cooking'));
    $properties['waiting'] = DataDefinition::create('integer')
      ->setLabel(t('Waiting'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'total' => [
          'type' => 'int',
          'unsigned' => TRUE,
        ],
        'serving' => [
          'type' => 'int',
          'unsigned' => TRUE,
        ],
        'preparation' => [
          'type' => 'int',
          'unsigned' => TRUE,
        ],
        'cooking' => [
          'type' => 'int',
          'unsigned' => TRUE,
        ],
        'waiting' => [
          'type' => 'int',
          'unsigned' => TRUE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $columns = ['total','serving','preparation','cooking','waiting'];
    foreach ($columns as $column){
      $value = $this->get($column)->getValue();
      if($value !== NULL && $value !== 0){
        return FALSE;
      }
    }
    return TRUE;
  }


}

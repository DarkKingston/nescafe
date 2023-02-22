<?php

namespace Drupal\dsu_c_core\Plugin\Field\FieldType;

use Drupal\Core\Field\Plugin\Field\FieldType\MapItem;

/**
 * Defines the 'serialized_settings_item' field type.
 *
 * @FieldType(
 *   id = "serialized_settings_item",
 *   label = @Translation("Serialized settings"),
 *   category = "Lightnest",
 *   cardinality = 1,
 * )
 */
class SerializedSettingsItem extends MapItem {

  /**
   * {@inheritdoc}
   */
  public function __get($name) {
    if (isset($this->values['value'][$name])) {
      return $this->values['value'][$name];
    }else{
      return parent::__get($name);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __isset($name) {
    return isset($this->values['value'][$name]) || parent::__isset($name);
  }
}

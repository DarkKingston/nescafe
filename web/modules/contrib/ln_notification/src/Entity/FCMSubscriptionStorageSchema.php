<?php

namespace Drupal\ln_notification\Entity;

use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the entity storage schema.
 */
class FCMSubscriptionStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getSharedTableFieldSchema(FieldStorageDefinitionInterface $storage_definition, $table_name, array $column_mapping): array {
    $schema = parent::getSharedTableFieldSchema($storage_definition, $table_name, $column_mapping);

    if ($storage_definition->getSetting('unique_key')) {
      $this->addSharedTableFieldUniqueKey($storage_definition, $schema);
    }

    return $schema;
  }

}

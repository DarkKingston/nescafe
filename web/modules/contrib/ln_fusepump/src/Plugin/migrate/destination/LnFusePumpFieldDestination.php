<?php

namespace Drupal\ln_fusepump\Plugin\migrate\destination;

use Drupal\migrate\Plugin\migrate\destination\EntityContentBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @MigrateDestination(
 *   id = "ln_fusepump_field_destination"
 * )
 */
class LnFusePumpFieldDestination extends EntityContentBase {

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\migrate\Plugin\MigrationInterface|null $migration
   *
   * @return \Drupal\Core\Plugin\ContainerFactoryPluginInterface|
   * \Drupal\ln_fusepump\Plugin\migrate\destination\LnFusePumpFieldDestination|
   * \Drupal\migrate\Plugin\migrate\destination\Entity|
   * \Drupal\migrate\Plugin\migrate\destination\EntityContentBase
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    $source = $migration->getSourceConfiguration();
    return parent::create($container, $configuration, 'entity:' . $source['entity_type'], $plugin_definition, $migration);
  }

  /**
   * @param \Drupal\migrate\Row $row
   * @param array $old_destination_id_values
   *
   * @return array|bool|void
   * @throws \Drupal\migrate\Exception\EntityValidationException
   * @throws \Drupal\migrate\MigrateException
   */
  public function import(Row $row, array $old_destination_id_values = []) {
    $source = $this->migration->getSourceConfiguration();
    if (isset($source['entity_type'])) {
      $entity_type_storage = \Drupal::entityTypeManager()
        ->getStorage($source['entity_type']);
      $entity = FALSE;
      $id_values = $row->getSource();
      if (isset($id_values['nid'])) {
        $entity = $entity_type_storage->load($id_values['nid']);
      }
      else {
        if (isset($id_values['title'])) {
          $entities = $entity_type_storage->loadByProperties([
            $entity_type_storage->getEntityType()
              ->getKey('label') => $id_values['title'],
          ]);
          if (!empty($entities)) {
            $entity = reset($entities);
          }
        }
      }

      if ($entity) {
        return parent::import($row, [$entity->id()]);
      }
    }


    return;
  }

}

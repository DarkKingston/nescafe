<?php

namespace Drupal\ln_adimo\Plugin\migrate\destination;

use Drupal\migrate\Plugin\migrate\destination\EntityContentBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @MigrateDestination(
 *   id = "ln_adimo_field_destination"
 * )
 */
class LnAdimoFieldDestination extends EntityContentBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    $source = $migration->getSourceConfiguration();
    return parent::create($container, $configuration, 'entity:' . $source['entity_type'], $plugin_definition, $migration);
  }

  /**
   * {@inheritdoc}
   */
  public function import(Row $row, array $old_destination_id_values = []) {
    $source = $this->migration->getSourceConfiguration();
    $id_values = $row->getSource();
    if (isset($source['entity_type'])) {
      $entity_type_storage = \Drupal::entityTypeManager()
        ->getStorage($source['entity_type']);
      $entity = FALSE;
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
      // Get field value of content type.
      $field = $source['field_name'];

      // Get adimo id from CSV file.
      $field_value = array_values($row->getDestination());

      if ($entity && $entity->hasField($field)) {
        // Get existing value of integration type.
        $integration_type_value = $entity->get($field)
                                    ->getValue()[0]['integrationType'];
        if (isset($source['widget_type'])) {
          $integration_type_value = $source['widget_type'];
        }
        // Get existing value of custom css type.
        $custom_css_value = $entity->get($field)->getValue()[0]['customCSS'];
        if (isset($source['custom_css'])) {
          $custom_css_value = $source['custom_css'];
        }
        // Get existing value of custom html type.
        $custom_html_value = $entity->get($field)
                               ->getValue()[0]['customButtonHTML'];
        if (isset($source['button_html'])) {
          $custom_html_value = $source['button_html'];
        }

        // Set values in entity and update it.
        $entity->set($field, [
          'integrationType'  => (!empty($integration_type_value) ? $integration_type_value : '5'),
          'touchpointID'     => (string) $id_values['adimo_id'],
          'customCSS'        => (!empty($custom_css_value) ? $custom_css_value : ''),
          'customButtonHTML' => (!empty($custom_html_value) ? $custom_html_value : ''),
        ]);
        $entity->save();
      }
    }
    return;
  }

}

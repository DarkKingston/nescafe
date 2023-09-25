<?php

namespace Drupal\advanced_datalayer;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a listing of Datalayer defaults entities.
 */
class AdvancedDatalayerDefaultsListBuilder extends ConfigEntityListBuilder {

  /**
   * The datalayer tag manager.
   *
   * @var \Drupal\advanced_datalayer\AdvancedDatalayerManagerInterface
   */
  protected $datalayerManager;

  /**
   * Constructs a new EncryptionProfileListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\advanced_datalayer\AdvancedDatalayerManagerInterface $datalayer_manager
   *   The datalayer manager.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, AdvancedDatalayerManagerInterface $datalayer_manager) {
    parent::__construct($entity_type, $storage);
    $this->datalayerManager = $datalayer_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('advanced_datalayer.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    $query = $this->getStorage()->getQuery()
      ->condition('id', 'global', '<>');

    // Only add the pager if a limit is specified.
    if ($this->limit) {
      $query->pager($this->limit);
    }

    $entity_ids = $query->execute();

    // Load global entity always.
    return $entity_ids + $this->getParentIds($entity_ids);
  }

  /**
   * Gets the parent entity ids for the list of entities to load.
   *
   * @param array $entity_ids
   *   The datalayer entity ids.
   *
   * @return array
   *   The list of parents to load
   */
  protected function getParentIds(array $entity_ids) {
    $parents = ['global' => 'global'];
    foreach ($entity_ids as $entity_id) {
      if (strpos($entity_id, '__') !== FALSE) {
        $entity_id_array = explode('__', $entity_id);
        $parent = reset($entity_id_array);
        $parents[$parent] = $parent;
      }
    }
    $parents_query = $this->getStorage()->getQuery()
      ->condition('id', $parents, 'IN');
    return $parents_query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Type');
    $header['settings'] = $this->t('Settings');
    $header['status'] = $this->t('Status');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['settings'] = $this->getSettings($entity);
    $row['status'] = $entity->status() ? $this->t('Enabled') : $this->t('Disabled');
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations(EntityInterface $entity) {
    $operations = parent::getOperations($entity);

    // Global and entity defaults can't be deleted.
    if (in_array($entity->id(), $this->datalayerManager::protectedDefaults(), TRUE)) {
      unset($operations['delete']);
    }

    return $operations;
  }

  /**
   * Renders the Datalayer defaults label and its configuration.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The Datalayer defaults entity.
   *
   * @return array
   *   Render array for a table cell.
   */
  public function getSettings(EntityInterface $entity) {

    $prefix = '';
    $inherits = '';
    if ($entity->id() !== 'global') {
      $inherits .= 'Global';
    }
    if (strpos($entity->id(), '__') !== FALSE) {
      $entity_label = explode(': ', $entity->label());
      $inherits .= ', ' . $entity_label[0];
    }

    if (!empty($inherits)) {
      $prefix = $this->t('Inherits tags from: @inherits', [
        '@inherits' => $inherits,
      ]);
    }

    $tags = $entity->get('tags');
    $all_groups = $this->datalayerManager->sortedGroups();
    $all_tags = $this->datalayerManager->sortedTags();
    foreach ($all_tags as $tag_name => $tag) {
      $tag_group = $tag['group'];
      if (isset($all_groups[$tag_group], $tags[$tag_name])) {
        $all_groups[$tag_group]['tags'][$tag_name] = $tags[$tag_name];
      }
    }

    $build['data'] = [
      '#type' => 'details',
      '#title' => $this->t('Settings'),
    ];
    $build['data']['table']['#prefix'] = $prefix;
    foreach ($all_groups as $group) {
      $rows = [];
      if (isset($group['tags'])) {
        foreach ($group['tags'] as $tag_id => $tag_value) {
          $rows[] = [
            $all_tags[$tag_id]['label'] . ' : ',
            $tag_value === '' ? $this->t('<pre>{Empty or Dynamic value}</pre>') : $tag_value,
          ];
        }

        $build['data']['table'][$group['id']] = [
          '#type' => 'table',
          '#prefix' => $group['id'] === 'root' ? '' : '<b><p>' . $group['label'] . '</p></b>',
          '#rows' => $rows,
        ];
      }
    }

    return $build;
  }

}

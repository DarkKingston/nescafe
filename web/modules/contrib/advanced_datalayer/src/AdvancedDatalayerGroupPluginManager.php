<?php

namespace Drupal\advanced_datalayer;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * A Plugin to manage your datalayer tag group.
 */
class AdvancedDatalayerGroupPluginManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    $subdir = 'Plugin/AdvancedDatalayer/Group';

    // The name of the annotation class that contains the plugin definition.
    $plugin_definition_annotation_name = 'Drupal\advanced_datalayer\Annotation\AdvancedDatalayerGroup';

    parent::__construct($subdir, $namespaces, $module_handler, NULL, $plugin_definition_annotation_name);

    $this->alterInfo('advanced_datalayer_groups');

    $this->setCacheBackend($cache_backend, 'advanced_datalayer_groups');
  }

}

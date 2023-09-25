<?php

namespace Drupal\ln_srh;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides an SRHProcess plugin manager.
 *
 * @see \Drupal\Core\Archiver\Annotation\Archiver
 * @see \Drupal\Core\Archiver\ArchiverInterface
 * @see plugin_api
 */
class SRHProcessManager extends DefaultPluginManager
{

  /**
   * Constructs a SRHProcessManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler)
  {
    parent::__construct(
      'Plugin/SRHProcess',
      $namespaces,
      $module_handler,
        'Drupal\ln_srh\SRHProcessInterface',
      'Drupal\ln_srh\Annotation\SRHProcess'
    );
    $this->defaults['srh_bundle'] = SRHConstants::SRH_RECIPE_BUNDLE;
    $this->defaults['srh_multilanguage'] = FALSE;
    $this->alterInfo('srh_process_info');
    $this->setCacheBackend($cache_backend, 'srh_process_info_plugins');
  }

  public function setProcessBundle($bundle) {
    $this->defaults['srh_bundle'] = $bundle;
  }

  public function setMultilanguage($multilanguage) {
    $this->defaults['srh_multilanguage'] = $multilanguage;
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = []) {
    if (!isset($configuration['srh_bundle'])) {
      $configuration['srh_bundle'] = $this->defaults['srh_bundle'];
    }
    if (!isset($configuration['srh_multilanguage'])) {
      $configuration['srh_multilanguage'] = $this->defaults['srh_multilanguage'];
    }
    return parent::createInstance($plugin_id, $configuration);
  }

}

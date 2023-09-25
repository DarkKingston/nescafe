<?php

namespace Drupal\dsu_core\Services;


/**
 * Provides methods to rewrite configuration.
 */
interface ConfigReplaceInterface {

  /**
   * Rewrites configuration for a given module.
   *
   * @param $module
   *   The name of a module (without the .module extension).
   * @param $dir
   *   The config directory name( ex: install, optional)
   */
  public function rewriteModuleConfig($module, $dir);
}

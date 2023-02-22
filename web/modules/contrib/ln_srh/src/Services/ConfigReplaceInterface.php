<?php

namespace Drupal\ln_srh\Services;


/**
 * Provides methods to rewrite configuration.
 */
interface ConfigReplaceInterface {

  /**
   * Rewrites configuration for a given module.
   *
   * @param $module
   *   The name of a module (without the .module extension).
   */
  public function rewriteModuleConfig($module);

  /**
   * @param array $original_config
   * @param array $rewrite
   * @param string $configName
   * @param string $extensionName
   *
   * @return array
   */
  public function rewriteConfig($original_config, $rewrite, $configName, $extensionName);

  /**
   * @param $ymlPath
   */
  public function createParagraphTypeConfigByYml($ymlPath);

  /**
   * @param $ymlPath
   * @param $fieldName
   * @param $configName
   */
  public function addFieldToEntityFormDisplayConfigByYml($ymlPath, $fieldName, $configName);

  /**
   * @param $ymlPath
   */
  public function createFieldStorageConfigByYml($ymlPath);

  /**
   * @param $ymlPath
   */
  public function createFieldConfigByYml($ymlPath);

  /**
   * @param $ymlPath
   */
  public function createEntityFormDisplayByYml($ymlPath);

  /**
   * @param $ymlPath
   */
  public function createEntityViewDisplayByYml($ymlPath);

}

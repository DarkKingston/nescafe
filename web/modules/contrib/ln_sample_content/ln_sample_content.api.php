<?php

/**
 * @file
 * Describe hooks provided by the Lightnest sample content module.
 */

/**
 * Provide sample content.
 *
 * By implementing hook_ln_sample_content(), a module can provide sample content through files.
 *
 * @return array
 * An array with file paths
 */
function hook_ln_sample_content() {
  /** @var \Drupal\Core\Extension\ModuleHandlerInterface $module_handler */
  $module_handler = \Drupal::service('module_handler');
  /** @var \Drupal\Core\Extension\Extension $module_object */
  $module_object = $module_handler->getModule(basename(__FILE__, '.module'));
  $module_name = $module_object->getName();

  $source = drupal_get_path('module', $module_name) . '/content';
  /** @var \Drupal\Core\File\FileSystemInterface $file_system */
  $file_system = \Drupal::service('file_system');
  $files = $file_system->scanDirectory($source, '/demo.*\.(yml)$/');
  $yaml_file_path = NULL;
  foreach ($files as $file) {
    $yaml_file_path[] = '/content/' . $file->filename;
  }

  return $yaml_file_path;
}

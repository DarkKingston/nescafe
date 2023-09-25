<?php

/**
 * @file
 * Sets up the update test with the module at 1.0.0 configuration.
 */

use Drupal\Core\Database\Database;
use Drupal\Component\Serialization\Yaml;

$connection = Database::getConnection();

// Enable google_optimize_js.
$extensions = $connection->select('config')
  ->fields('config', ['data'])
  ->condition('collection', '')
  ->condition('name', 'core.extension')
  ->execute()
  ->fetchField();

$extensions = unserialize($extensions, ['allowed_classes' => []]);
$extensions['module']['google_optimize_js'] = 0;
$connection->update('config')
  ->fields(['data' => serialize($extensions)])
  ->condition('collection', '')
  ->condition('name', 'core.extension')
  ->execute();

// Set the configuration for 1.0.0.
$initial_config = Yaml::decode(file_get_contents(__DIR__ . '/google_optimize_js-1.0.0.settings.yml'));

$fields = ['collection', 'name', 'data'];

$values = [
  'collection' => '',
  'name' => 'google_optimize_js.settings',
  'data' => serialize($initial_config),
];

$connection->insert('config')
  ->fields($fields)
  ->values($values)
  ->execute();

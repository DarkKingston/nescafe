<?php

/**
 * @file
 * Contains post-update hooks.
 */

/**
 * Sets the proper default configuration for loading_strategy.
 */
function google_optimize_js_post_update_loading_strategy(&$sandbox) {
  $config = \Drupal::configFactory()->getEditable('google_optimize_js.settings');
  $config->set('loading_strategy', 'synchronous');
  $config->save();
}

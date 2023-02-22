<?php

namespace Drupal\ln_srh\Commands;

use Drupal;
use Drush\Commands\DrushCommands;

/**
 * Class GetRecipes.
 *
 * Drush 9 commands.
 *
 * @package Drupal\ln_srh\Commands
 */
class SyncRecipesQueue extends DrushCommands {

  /**
   * Get recipes from Smart Recipe Hub.
   *
   * @command ln_srh:recipes-synchronize-queue
   * @aliases srh-recipes-sync-queue
   */
  public function synchronize() {
    /** @var \Drupal\ln_srh\Services\SRHUtils $srh_utils */
    $srh_utils = \Drupal::service('ln_srh.utils');
    $srh_utils->syncRecipes();
    $srh_utils->processQueueSync();
  }

}

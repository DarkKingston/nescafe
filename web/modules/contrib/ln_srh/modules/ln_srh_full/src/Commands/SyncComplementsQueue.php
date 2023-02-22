<?php

namespace Drupal\ln_srh_full\Commands;

use Drupal;
use Drush\Commands\DrushCommands;

/**
 * Class SyncComplementsQueue.
 *
 * Drush 9 commands.
 *
 * @package Drupal\ln_srh_full\Commands
 */
class SyncComplementsQueue extends DrushCommands {

  /**
   * Get complements from Smart Recipe Hub.
   *
   * @command ln_srh_full:complements-synchronize-queue
   * @aliases srh-complements-sync-queue
   */
  public function synchronize() {
    /** @var \Drupal\ln_srh\Services\SRHUtilsInterface $srh_utils */
    $srh_utils = \Drupal::service('ln_srh.utils');
    /** @var \Drupal\ln_srh_full\Services\SRHComplementUtilsInterface $srh_utils_complements */
    $srh_utils_complements = \Drupal::service('ln_srh_full.complement_utils');
    $srh_utils_complements->syncComplements();
    $srh_utils->processQueueSync('srh_complement');
  }

}

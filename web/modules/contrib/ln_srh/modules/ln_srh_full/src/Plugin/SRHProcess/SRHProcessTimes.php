<?php

namespace Drupal\ln_srh_full\Plugin\SRHProcess;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\ln_srh\Plugin\SRHProcess\SRHProcessBase;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_times",
 *   field_name = "field_srh_times",
 *   label = @Translation("Times")
 * )
 */

class SRHProcessTimes extends SRHProcessBase {

  public function process(ContentEntityInterface $entity, $srh_data, $field_name){
    $times = $srh_data['time'] ?? FALSE;
    if($times){
      return [
        'total' => $times['total'] ?? 0,
        'serving' => $times['serving'] ?? 0,
        'preparation' => $times['preparation'] ?? 0,
        'cooking' => $times['cooking'] ?? 0,
        'waiting' => $times['waiting'] ?? 0,
      ];
    }
    return NULL;
  }

}

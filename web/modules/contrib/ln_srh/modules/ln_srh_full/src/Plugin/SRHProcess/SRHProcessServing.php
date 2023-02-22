<?php

namespace Drupal\ln_srh_full\Plugin\SRHProcess;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\ln_srh\Plugin\SRHProcess\SRHProcessBase;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_serving",
 *   field_name = "field_srh_serving",
 *   label = @Translation("Serving")
 * )
 */

class SRHProcessServing extends SRHProcessBase {

  public function process(ContentEntityInterface $entity, $srh_data, $field_name){
    $serving = $srh_data['servings'] ?? FALSE;
    if($serving){
      return [
        'number' => $serving['number'] ?? 0,
        'display_name' => $serving['displayName'] ?? '',
      ];
    }
    return NULL;
  }

}

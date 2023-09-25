<?php

namespace Drupal\ln_srh_basic\Plugin\SRHProcess;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\ln_srh\Plugin\SRHProcess\SRHProcessParagraph;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_steps_basic",
 *   field_name = "field_srh_steps",
 *   label = @Translation("Steps Basic")
 * )
 */

class SRHProcessStepsBasic extends SRHProcessParagraph {

  protected function getSRHEntityReferenceData($srh_data) {
    $steps = $srh_data['steps'] ?? [];
    if(!empty($steps)){
      $order = array_column($steps, 'number');
      array_multisort($order, SORT_ASC, $steps);
    }
    return $steps;
  }

  public function getValues($srh_data, $langcode) {
    return [
      'type' => 'srh_step',
      'field_c_text' => $srh_data['text'] ?? $srh_data['title'],
    ];
  }

}

<?php

namespace Drupal\ln_srh_full\Plugin\SRHProcess;

use Drupal\ln_srh_extended\Plugin\SRHProcess\SRHProcessStepsExtended;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_steps_full",
 *   field_name = "field_srh_steps",
 *   label = @Translation("Steps Full")
 * )
 */

class SRHProcessStepsFull extends SRHProcessStepsExtended {

  public function getValues($srh_data, $langcode) {
    $values = parent::getValues($srh_data, $langcode);
    $values += [
      'field_srh_recipe_step_id' => $srh_data['id'] ?? '',
    ];
    return $values;
  }

}

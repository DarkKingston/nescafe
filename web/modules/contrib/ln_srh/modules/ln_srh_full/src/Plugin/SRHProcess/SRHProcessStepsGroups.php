<?php

namespace Drupal\ln_srh_full\Plugin\SRHProcess;

use Drupal\ln_srh\Plugin\SRHProcess\SRHProcessParagraph;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_steps_groups",
 *   field_name = "field_srh_steps_groups",
 *   label = @Translation("Steps Groups")
 * )
 */

class SRHProcessStepsGroups extends SRHProcessParagraph {

  protected function getSRHEntityReferenceData($srh_data) {
    return $srh_data['stepGroups'] ?? [];
  }

  public function getValues($srh_data, $langcode) {
    return [
      'type' => 'srh_step_group',
      'field_c_title' => $srh_data['name'] ?? '',
      'field_srh_recipe_steps_ids' => $srh_data['stepIds'] ?? []
    ];
  }

}

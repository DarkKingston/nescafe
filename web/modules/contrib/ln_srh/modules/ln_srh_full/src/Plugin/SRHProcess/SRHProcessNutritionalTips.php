<?php

namespace Drupal\ln_srh_full\Plugin\SRHProcess;

use Drupal\ln_srh_standard\Plugin\SRHProcess\SRHProcessTipsStandard;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_nutritional_tips",
 *   field_name = "field_srh_nutritional_tips",
 *   label = @Translation("Tips standard")
 * )
 */

class SRHProcessNutritionalTips extends SRHProcessTipsStandard {

  protected function getSRHEntityReferenceData($srh_data) {
    $tips = $srh_data['tips']['nutritional'] ?? [];
    if(!empty($tips)){
      $order = array_column($tips, 'order');
      array_multisort($order, SORT_ASC, $tips);
    }
    return $tips;
  }

}

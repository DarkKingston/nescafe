<?php

namespace Drupal\ln_campaign\Plugin\Action;
use Drupal\ln_campaign\LnCampaignConstants;

/**
 * Change to validate status a webform submission.
 *
 * @Action(
 *   id = "ln_campaign_submission_validate_action",
 *   label = @Translation("Change to Validate status"),
 *   type = "webform_submission"
 * )
 */
class LnCValidateSubmission extends LnCTransitionStateSubmission {

  public function getTransitionId(){
    return LnCampaignConstants::LN_CAMPAING_WORKFLOWS_TRANSITION_VALIDATION;
  }

}

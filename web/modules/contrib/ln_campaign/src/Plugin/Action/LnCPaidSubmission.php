<?php

namespace Drupal\ln_campaign\Plugin\Action;

use Drupal\ln_campaign\LnCampaignConstants;

/**
 * Change to paid status a webform submission.
 *
 * @Action(
 *   id = "ln_campaign_submission_paid_action",
 *   label = @Translation("Change to Paid status"),
 *   type = "webform_submission"
 * )
 */
class LnCPaidSubmission extends LnCTransitionStateSubmission {

  public function getTransitionId() {
    return LnCampaignConstants::LN_CAMPAING_WORKFLOWS_TRANSITION_PAID;
  }

}

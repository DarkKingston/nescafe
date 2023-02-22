<?php

namespace Drupal\ln_campaign\Plugin\Action;

use Drupal\ln_campaign\LnCampaignConstants;

/**
 * Change to pending status a webform submission.
 *
 * @Action(
 *   id = "ln_campaign_submission_pending_action",
 *   label = @Translation("Change to Pending status"),
 *   type = "webform_submission"
 * )
 */
class LnCPendingSubmission extends LnCTransitionStateSubmission {

  public function getTransitionId() {
    return LnCampaignConstants::LN_CAMPAING_WORKFLOWS_TRANSITION_PENDING;
  }

}

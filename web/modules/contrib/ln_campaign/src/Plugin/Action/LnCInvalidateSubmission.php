<?php

namespace Drupal\ln_campaign\Plugin\Action;

use Drupal\ln_campaign\LnCampaignConstants;

/**
 * Change to invalidate status a webform submission.
 *
 * @Action(
 *   id = "ln_campaign_submission_invalidate_action",
 *   label = @Translation("Change to Invalidate status"),
 *   type = "webform_submission"
 * )
 */
class LnCInvalidateSubmission extends LnCTransitionStateSubmission {

  public function getTransitionId() {
      return LnCampaignConstants::LN_CAMPAING_WORKFLOWS_TRANSITION_INVALIDATION;
  }

}

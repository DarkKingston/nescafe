<?php

namespace Drupal\ln_campaign\Plugin\WebformHandler;

use Drupal\ln_campaign\Entity\LnCampaign;
use Drupal\ln_campaign\LnCampaignConstants;
use Drupal\ln_campaign\Service\LnCWorkflowsManager;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Set if you have won or lost depending on the time of participation.
 *
 * @WebformHandler(
 *   id = "ln_campaign_pin",
 *   label = @Translation("Set winner entry by pin."),
 *   category = @Translation("Winning Moment"),
 *   description = @Translation("Set if you have won or lost depending on the pin selected."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */

class LnCPinHandler extends WebformHandlerBase {

  /**
   * @var LnCWorkflowsManager
   */
  protected $workflowsManager;

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->workflowsManager = $container->get('ln_campaign_workflows_element.manager');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */

  public function preSave(WebformSubmissionInterface $webform_submission) {
    parent::preSave($webform_submission);
    // Get an array of the values from the submission.
    $values = $webform_submission->getData();
    if($webform_submission->isNew() && isset($values['ln_campaign_id']) && $campaign = LnCampaign::load($values['ln_campaign_id'])){
      if (isset($values[LnCampaignConstants::LN_CAMPAING_WEBFORM_PINCODE_FIELD]) && $campaign->hasField(LnCampaignConstants::LN_CAMPAING_PINCODE_FIELD)) {
        $workflowType = $this->workflowsManager->getWorkflowType(LnCampaignConstants::LN_CAMPAING_WORKFLOWS_CATEGORY);
        $pincodes = $campaign->get(LnCampaignConstants::LN_CAMPAING_PINCODE_FIELD);
        $winner = FALSE;
        foreach ($pincodes as $pincode) {
          if ($pincode->getString() == $values[LnCampaignConstants::LN_CAMPAING_WEBFORM_PINCODE_FIELD]) {
            $winner = TRUE;
            break;
          }
        }
        if(!$winner){
          $values[LnCampaignConstants::LN_CAMPAING_WEBFORM_WORKFLOW_FIELD] = [
            'workflow_state' => $workflowType->getState(LnCampaignConstants::LN_CAMPAING_WORKFLOWS_STATE_INVALID)->id(),
            'workflow_state_label' => $workflowType->getState(LnCampaignConstants::LN_CAMPAING_WORKFLOWS_STATE_INVALID)->label(),
            'workflow_state_markup' => $workflowType->getState(LnCampaignConstants::LN_CAMPAING_WORKFLOWS_STATE_INVALID)->label(),
          ];
          $webform_submission->setData($values);
        }
      }
    }
  }

}

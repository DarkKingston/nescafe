<?php

namespace Drupal\ln_campaign\Plugin\WebformHandler;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\ln_campaign\Entity\LnCampaign;
use Drupal\ln_campaign\LnCampaignConstants;
use Drupal\ln_campaign\LnCampaignInterface;
use Drupal\ln_campaign\Service\LnCWorkflowsManager;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Set if you have won or lost depending on the time of participation.
 *
 * @WebformHandler(
 *   id = "ln_campaign_winnign_moment",
 *   label = @Translation("Set winner entry by moment."),
 *   category = @Translation("Winning Moment"),
 *   description = @Translation("Set if you have won or lost depending on the time of participation."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */

class LnCWinningMomentHandler extends WebformHandlerBase {

  /**
   * @var LnCWorkflowsManager
   */
  protected $workflowsManager;

  /**
   * {@inheritdoc}
   */
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
      if($campaign->hasField(LnCampaignConstants::LN_CAMPAING_MOMENT_FIELD) && !$campaign->get(LnCampaignConstants::LN_CAMPAING_MOMENT_FIELD)->isEmpty()){
        $submission_date = DrupalDateTime::createFromTimestamp($webform_submission->getCompletedTime());
        $workflowType = $this->workflowsManager->getWorkflowType(LnCampaignConstants::LN_CAMPAING_WORKFLOWS_CATEGORY);
        $winner = FALSE;
        foreach($campaign->get(LnCampaignConstants::LN_CAMPAING_MOMENT_FIELD) as $winning_moment){
          /** @var DrupalDateTime $wm_start */
          $wm_start = $winning_moment->start_date;
          /** @var DrupalDateTime $wm_end */
          $wm_end = $winning_moment->end_date;
          $winners = $this->getWinningMomentWinners($campaign, $wm_start->getTimestamp(), $wm_end->getTimestamp());
          if($submission_date >= $wm_start && $submission_date <= $wm_end && $winners < 1){
            $winner = TRUE;
            break;
          }
        }
        if(!$winner){
          $values[LnCampaignConstants::LN_CAMPAING_WEBFORM_WORKFLOW_FIELD] = [
            'workflow_state' => $workflowType->getState('ln_campaign_invalid')->id(),
            'workflow_state_label' => $workflowType->getState('ln_campaign_invalid')->label(),
            'workflow_state_markup' => $workflowType->getState('ln_campaign_invalid')->label(),
          ];
          $webform_submission->setData($values);
        }
      }
    }
  }

  /**
   * @param LnCampaignInterface $campaign
   * @param int $wm_start
   * @param int $wm_end
   * @return int
   */
  private function getWinningMomentWinners(LnCampaignInterface $campaign, $wm_start, $wm_end){
    $query = \Drupal::database()->select('webform_submission', 'submission');
    $query->join('webform_submission_data', 'data', 'submission.sid = data.sid');
    $query->addField('submission', 'sid');
    $query->condition('submission.webform_id', $this->getWebform()->id());
    $query->condition('submission.entity_id', $campaign->id());
    $query->condition('submission.entity_type', $campaign->getEntityTypeId());
    $query->condition('submission.completed', $wm_start, '>=');
    $query->condition('submission.completed', $wm_end, '<');
    $query->condition('data.property', 'workflow_state');
    $query->condition('data.value', LnCampaignConstants::LN_CAMPAING_WORKFLOWS_STATE_INVALID, '<>');

    return count($query->execute()->fetchCol());
  }
}

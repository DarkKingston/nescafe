<?php

namespace Drupal\ln_campaign\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ln_campaign\Entity\LnCampaign;
use Drupal\ln_campaign\LnCampaignConstants;
use Drupal\ln_campaign\LnCampaignInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Validate unique email by campaign.
 *
 * @WebformHandler(
 *   id = "ln_campaign_email_validate",
 *   label = @Translation("Validate unique email for campaign."),
 *   category = @Translation("Validation"),
 *   description = @Translation("Validate unique email for campaign."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */

class LnCEmailValidateHandler extends WebformHandlerBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $this->validateUniqueEmail($form,$form_state,$webform_submission);
  }

  /**
   * Validate unique email.
   */
  private function validateUniqueEmail($form,FormStateInterface $formState, WebformSubmissionInterface $webform_submission) {
    $values = $webform_submission->getData();
    if($webform_submission->isNew() && isset($values['ln_campaign_id']) && $campaign = LnCampaign::load($values['ln_campaign_id'])){
      $email = $formState->getValue(LnCampaignConstants::LN_CAMPAING_WEBFORM_EMAIL_FIELD);
      $count = $this->getSubmissionsCountByEmail($campaign,$email);
      if($count > 0){
        $formState->setErrorByName(LnCampaignConstants::LN_CAMPAING_WEBFORM_EMAIL_FIELD, $this->t('A user with email @email has already participated in this campaign.',['@email' => $email]));
      }
    }
  }

  private function getSubmissionsCountByEmail(LnCampaignInterface $campaign, $email){
    $query = \Drupal::database()->select('webform_submission', 'submission');
    $query->join('webform_submission_data', 'data', 'submission.sid = data.sid');
    $query->addField('submission', 'sid');
    $query->condition('submission.webform_id', $this->getWebform()->id());
    $query->condition('submission.entity_id', $campaign->id());
    $query->condition('submission.entity_type', $campaign->getEntityTypeId());
    $query->condition('data.name', LnCampaignConstants::LN_CAMPAING_WEBFORM_EMAIL_FIELD);
    $query->condition('data.value', $email);

    return count($query->execute()->fetchCol());
  }

}

<?php

namespace Drupal\ln_campaign;

use Drupal\Core\Entity\EntityInterface;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\WebformSubmissionListBuilder;

/**
 * Provides a list controller for webform submission entity.
 *
 * @ingroup webform
 */
class LnCWebformSubmissionListBuilder extends WebformSubmissionListBuilder {

  /**
   * @var array
   */
  public $workflowFields = [];

  /**
   * Initialize WebformSubmissionListBuilder object.
   */
  protected function initialize() {
    parent::initialize();
    if($this->webform && $this->webform->get('category') == LnCampaignConstants::LN_CAMPAING_WEBFORM_CATEGORY){
      $query = $this->request->query->all();
      foreach ($query as $key => $value) {
        if (strpos($key, 'workflow-') === 0) {
          $this->workflowFields[$key] = $value;
        }
      }
    }
  }
  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    if($this->webform && $this->webform->get('category') == LnCampaignConstants::LN_CAMPAING_WEBFORM_CATEGORY){
      unset($build['#attached']);
      unset($build['custom_top']);
      $build['table'] = \Drupal::formBuilder()->getForm('\Drupal\ln_campaign\Form\LnCWebformSubmissionBulkForm', $build['table'], $this->webform->access('delete any webform submission'));
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    $ids = parent::getEntityIds();
    $finalIds = $ids;
    if($this->webform && $this->webform->get('category') == LnCampaignConstants::LN_CAMPAING_WEBFORM_CATEGORY){
      $finalIds = $this->filterIdsByState($ids);
      if (count($ids) != count($finalIds)) {
        $this->total = count($finalIds);
      }
    }

    return $finalIds;
  }

  /**
   * Filter out any submission ids not at the given state.
   *
   *
   * @param array $ids
   * @param string $state
   *
   * @return array
   */
  protected function filterIdsByState(array $ids, string $state = NULL) {
    if (count($this->workflowFields) == 0) {
      return $ids;
    }
    $finalIds = [];
    foreach ($ids as $id) {
      foreach ($this->workflowFields as $workflowElement => $filterValue) {
        $submission = WebformSubmission::load($id);
        if ($this->submissionHasState($submission, $workflowElement, $filterValue)) {
          $finalIds[] = $id;
        }
      }
    }
    return $finalIds;
  }

  /**
   * Check if submission is at a current state for an element.
   *
   * @param WebformSubmissionInterface $submission
   * @param mixed $workflowElement
   * @param string $state
   *
   * @return bool
   *   TRUE if submission is at current state for element
   */
  protected function submissionHasState(WebformSubmissionInterface $submission, $workflowElement, string $state = NULL) {
    if (!$state || $state == '') {
      return FALSE;
    }
    $submissionValue = $submission->getElementData(str_replace('workflow-', '', $workflowElement));
    return $submissionValue && $submissionValue['workflow_state'] == $state;
  }

  /**
   * Build the filter form.
   *
   * @return array
   *   A render array representing the filter form.
   */
  protected function buildFilterForm() {
    $filter_form = parent::buildFilterForm();
    if($this->webform && $this->webform->get('category') == LnCampaignConstants::LN_CAMPAING_WEBFORM_CATEGORY){
      // State options.
      $state_options = [
        '' => $this->t('All sasdf[@total]', ['@total' => $this->getTotal(NULL, NULL, $this->sourceEntityTypeId)]),
        static::STATE_STARRED => $this->t('Starred [@total]', ['@total' => $this->getTotal(NULL, static::STATE_STARRED, $this->sourceEntityTypeId)]),
        static::STATE_UNSTARRED => $this->t('Unstarred [@total]', ['@total' => $this->getTotal(NULL, static::STATE_UNSTARRED, $this->sourceEntityTypeId)]),
        static::STATE_LOCKED => $this->t('Locked [@total]', ['@total' => $this->getTotal(NULL, static::STATE_LOCKED, $this->sourceEntityTypeId)]),
        static::STATE_UNLOCKED => $this->t('Unlocked [@total]', ['@total' => $this->getTotal(NULL, static::STATE_UNLOCKED, $this->sourceEntityTypeId)]),
      ];
      // Add draft to state options.
      if (!$this->webform || $this->webform->getSetting('draft') !== WebformInterface::DRAFT_NONE) {
        $state_options += [
          static::STATE_COMPLETED => $this->t('Completed [@total]', ['@total' => $this->getTotal(NULL, static::STATE_COMPLETED, $this->sourceEntityTypeId)]),
          static::STATE_DRAFT => $this->t('Draft [@total]', ['@total' => $this->getTotal(NULL, static::STATE_DRAFT, $this->sourceEntityTypeId)]),
        ];
      }

      // Source entity options.
      if ($this->webform && !$this->sourceEntity) {
        // < 100 source entities a select menuwill be displayed.
        // > 100 source entities an autocomplete input will be displayed.
        $source_entity_total = $this->storage->getSourceEntitiesTotal($this->webform);
        if ($source_entity_total < 100) {
          $source_entity_options = $this->storage->getSourceEntitiesAsOptions($this->webform);
          $source_entity_default_value = $this->sourceEntityTypeId;
        }
        elseif ($this->sourceEntityTypeId && strpos($this->sourceEntityTypeId, ':') !== FALSE) {
          $source_entity_options = $this->webform;
          try {
            list($source_entity_type, $source_entity_id) = explode(':', $this->sourceEntityTypeId);
            $source_entity = $this->entityTypeManager->getStorage($source_entity_type)->load($source_entity_id);
            $source_entity_default_value = $source_entity->label() . " ($source_entity_type:$source_entity_id)";
          }
          catch (\Exception $exception) {
            $source_entity_default_value = '';
          }
        }
        else {
          $source_entity_options = $this->webform;
          $source_entity_default_value = '';
        }
      }
      else {
        $source_entity_options = NULL;
        $source_entity_default_value = '';
      }
      $filter_form = $this->formBuilder->getForm('\Drupal\ln_campaign\Form\LnCWebformSubmissionFilterForm', $this->keys, $source_entity_default_value, $source_entity_options);
    }

    return $filter_form;
  }


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = parent::buildHeader();
    if($this->webform && $this->webform->get('category') == LnCampaignConstants::LN_CAMPAING_WEBFORM_CATEGORY){
      $header['entity'] = ['data'=> $this->t('Campaign')];
      unset($header['remote_addr']);
      unset($header['element__' . LnCampaignConstants::LN_CAMPAING_WEBFORM_TICKET_FIELD]);
      $header['element__' . LnCampaignConstants::LN_CAMPAING_WEBFORM_WORKFLOW_FIELD]['field'] = 'element__' . LnCampaignConstants::LN_CAMPAING_WEBFORM_WORKFLOW_FIELD;
      $header['element__' . LnCampaignConstants::LN_CAMPAING_WEBFORM_WORKFLOW_FIELD]['specifier'] = 'element__' . LnCampaignConstants::LN_CAMPAING_WEBFORM_WORKFLOW_FIELD;
    }

    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = parent::buildRow($entity);
    if($this->webform && $this->webform->get('category') == LnCampaignConstants::LN_CAMPAING_WEBFORM_CATEGORY){
      unset($row['data']['remote_addr']);
      unset($row['data']['element__' . LnCampaignConstants::LN_CAMPAING_WEBFORM_TICKET_FIELD]);
    }

    return $row;
  }
}

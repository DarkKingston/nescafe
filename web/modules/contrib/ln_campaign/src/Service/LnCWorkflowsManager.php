<?php

namespace Drupal\ln_campaign\Service;

use Drupal\Core\Render\Element;
use Drupal\Core\Session\AccountInterface;
use Drupal\ln_campaign\LnCampaignConstants;
use Drupal\webform\Plugin\WebformElementManagerInterface;
use Drupal\webform\WebformInterface;
use Drupal\workflows\Entity\Workflow;
use Drupal\workflows\Transition;

/**
 * Class LnCWorkflowsManager.
 *
 */
class LnCWorkflowsManager {

  /**
   * @var WebformElementManagerInterface
   */
  protected $webformElementManager;

  public function __construct(WebformElementManagerInterface $webformElementManager){
    $this->webformElementManager = $webformElementManager;
  }

  /**
   * Get workflow type for a workflow.
   *
   * @param string $workflowId
   *   String ID.
   *
   * @return \Drupal\workflows\WorkflowTypeInterface
   *   Workflow type.
   */
  public function getWorkflowType($workflowId) {
    /** @var \Drupal\workflows\WorkflowInterface $workflow */
    $workflow = Workflow::load($workflowId);
    if (!$workflow) {
      return NULL;
    }
    /** @var \Drupal\ln_campaign\Plugin\WorkflowType\LnCWorkflowsElement $workflowType */
    $workflowType = $workflow->getTypePlugin();
    return $workflowType;
  }

  /**
   * Get workflow type from workflow for element.
   *
   * @param array $element
   *   Workflows element.
   *
   * @return \Drupal\workflows\WorkflowTypeInterface
   *   Workflow type.
   */
  public function getWorkflowTypeFromElement(array $element) {
    return isset($element['#workflow']) ? $this->getWorkflowType($element['#workflow']) : NULL;
  }

  /**
   * Get state for an element with the state ID, or return NULL.
   *
   * @param mixed $element
   *   Workflows element.
   * @param mixed $id
   *   State ID.
   *
   * @return \Drupal\workflows\StateInterface
   *   The workflow state.
   */
  public function getStateFromElementAndId($element, $id) {
    $workflowType = $this->getWorkflowTypeFromElement($element);
    $state = $workflowType->hasState($id) ? $workflowType->getState($id) : NULL;
    return $state;
  }

  /**
   * Get the initial workflow state for the workflow of the element.
   *
   * @param array $element
   *   Workflows element.
   *
   * @return \Drupal\workflows\StateInterface
   *   The workflow state.
   */
  public function getInitialStateForElement(array $element) {
    $workflowType = $this->getWorkflowTypeFromElement($element);
    return $workflowType ? $workflowType->getInitialState() : NULL;
  }

  /**
   * Get all tranistions for a current state for a workflow.
   *
   * Optionally also filter by user access.
   *
   * @param string $workflowId
   * @param string $currentStateId
   * @param AccountInterface $account
   * @param WebformInterface $webform
   *
   * @return array
   *   Array of WorkflowTransitions
   */
  public function getAvailableTransitionsForWorkflow(string $workflowId, string $currentStateId, AccountInterface $account = NULL, WebformInterface $webform = NULL) {
    $workflowType = $this->getWorkflowType($workflowId);
    if (!$workflowType) {
      return [];
    }
    /** @var \Drupal\workflows\WorkflowInterface $workflow */
    $workflow = Workflow::load($workflowId);
    if ($currentStateId && $workflowType->hasState($currentStateId)) {
      $currentState = $workflowType->getState($currentStateId);
    } else {
      $currentState = $workflowType->getInitialState();
    }
    if (!$currentState) {
      return [];
    }

    return $this->getValidTransitions($workflow, $currentState, $account, $webform);
  }

  public function getValidTransitions($workflow, $state, $account = NULL, WebformInterface $webform = NULL) {
    // Get available transitions from current state:
    $availableTransitions = $state->getTransitions();
    if ($webform && $account) {
      foreach ($availableTransitions as $transition_id => $transition) {
        $access = $this->checkAccessForSubmissionAndTransition($workflow, $account, $webform, $transition);
        if (!$access) {
          unset($availableTransitions[$transition_id]);
        }
      }
    }

    return $availableTransitions;
  }

  /**
   * Check if user can do a transition for a workflow for a webform.
   *
   * @param Workflow $workflow
   * @param AccountInterface $account
   * @param WebformInterface $webform
   * @param Transition $transition
   *
   * @return bool
   *   Whether user can transition the workflow.
   */
  public function checkAccessForSubmissionAndTransition(Workflow $workflow, AccountInterface $account, WebformInterface $webform, Transition $transition) {
    $pass = FALSE;
    $workflow_elements = $this->getWorkflowElementsForWebform($webform);
    foreach ($workflow_elements as $element) {
      if ($element['#workflow'] != $workflow->id()) {
        continue;
      }
      $transition_access_id = 'access_transition_' . $transition->id();
      if (isset($element[$transition_access_id . '_workflow_enabled']) && !isset($element[$transition_access_id . '_workflow_enabled'])) {
        return FALSE;
      }
      /** @var \Drupal\webform\Plugin\WebformElementInterface $element_plugin */
      $element_plugin = $this->webformElementManager->getElementInstance($element, $webform);
      $pass = $element_plugin->checkAccessRules('transition_' . $transition->id(), $element, $account);
    }

    return $pass;
  }

  /**
   * Recursively filter webform elements to only keep ln_campaign_workflows_element.
   *
   * @param array $elements
   *   Webform elements returned by $webform->getElementsOriginalDecoded().
   *
   * @return array
   *   Filtered webform elements keyed by machine name.
   */
  protected function filterWorkflowElements($elements) {
    $filtered = [];
    foreach (Element::children($elements) as $key) {
      $element = $elements[$key];
      if (isset($element['#type']) && $element['#type'] == LnCampaignConstants::LN_CAMPAING_WORKFLOWS_ELEMENT) {
        $filtered[$key] = $element;
      }
      $filtered += $this->filterWorkflowElements($element);
    }
    return $filtered;
  }


  /**
   * Get all workflow elements for a webform.
   *
   * @param WebformInterface $webform
   *   Webform.
   *
   * @return array
   *   Array of elements arrays.
   */
  public function getWorkflowElementsForWebform(WebformInterface $webform) {
    if (!$webform) {
      return [];
    }
    $elements = $webform->getElementsOriginalDecoded();
    $workflow_elements = $this->filterWorkflowElements($elements);

    return $workflow_elements;
  }

  /**
   * Get all transitions for a given workflow.
   *
   * This is not limited by what's possible for a given submission.
   *
   * @param string $workflow_id
   *   Workflow ID.
   *
   * @return array
   *   Array of transitions or NULL;
   */
  public function getTransitionsForWorkflow(string $workflow_id) {
    $workflowType = $this->getWorkflowType($workflow_id);
    if (!$workflowType) {
      return NULL;
    }
    return $workflowType->getTransitions();
  }
}

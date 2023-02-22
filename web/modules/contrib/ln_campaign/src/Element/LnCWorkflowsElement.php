<?php

namespace Drupal\ln_campaign\Element;

use Drupal\ln_campaign\Service\LnCWorkflowsManager;
use Drupal\user\Entity\User;
use Drupal\webform\Element\WebformCompositeBase;
use Drupal\webform\Entity\Webform;

/**
 * Provides a 'ln_campaign_workflows_element'.
 *
 *
 * @FormElement("ln_campaign_workflows_element")
 */
class LnCWorkflowsElement extends WebformCompositeBase {

  /**
   * {@inheritdoc}
   */
  public static function getCompositeElements(array $element) {
    /** @var  LnCWorkflowsManager $workflowsManager */
    $workflowsManager = \Drupal::service('ln_campaign_workflows_element.manager');
    $elements = [];
    $state = NULL;
    if (isset($element['#value']['workflow_state']) && $element['#value']['workflow_state'] != '') {
      $state = $workflowsManager->getStateFromElementAndId($element, $element['#value']['workflow_state']);
    }
    if (!$state) {
      $state = $workflowsManager->getInitialStateForElement($element);
    }
    // Set hidden values to manage the states:
    $elements['workflow_state'] = [
      '#title' => t('Workflow state'),
      '#type'  => 'hidden',
    ];
    $elements['workflow_state_previous'] = [
      '#title' => t('Previous workflow state'),
      '#type'  => 'hidden',
    ];
    $elements['workflow_state_label'] = [
      '#title' => t('Workflow state label'),
      '#type'  => 'hidden',
    ];
    $elements['workflow_fieldset'] = [
      '#title' => $element['#title'] ?? t('Workflow'),
      '#type'   => 'details',
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];
    // Show the user the current state value.
    $elements['workflow_fieldset']['workflow_state_markup'] = [
      '#title'         => t('Current workflow state'),
      '#type'          => 'markup',
      '#markup'        => $state ? $state->label() : t('Not set'),
    ];
    // Allow user to select a transition if there are any available:
    $availableTransitions = static::getAvailableTransitions($element);
    // If setting enabled, hide completely
    if (count($availableTransitions) == 0 && isset($element['#hide_if_no_transitions']) && $element['#hide_if_no_transitions']) {
      return [];
    }
    if (count($availableTransitions) > 0) {
      $elements['workflow_fieldset']['transition'] = [
        '#title'         => t('Transition'),
        '#type'          => 'select',
        '#description'   => t('Some transitions may be hidden if you do not have access, e.g. certain roles.'),
        '#options'       => static::convertTransitionsToOptions($availableTransitions),
        '#required' => isset($element['#require_transition_if_available']) ? $element['#require_transition_if_available'] : FALSE,
        '#attributes'    => [
          'class' => ['workflow-transition'],
        ]
      ];
    } else {
      $elements['workflow_fieldset']['transitions_message'] = [
        '#title'  => t('Transitions'),
        '#type'   => 'markup',
        '#markup' => t("No transitions are available to you. You may not have the required access, or the workflow may have reached the end of a process."),
      ];
      $elements['transition'] = [
        '#title'  => t('Transition'),
        '#type'   => 'hidden',
      ];
    }

    return $elements;
  }

  /**
   * Get the available transitions for an element of a submission.
   *
   * @param array $element
   *   Workflow element array.
   * @param bool $checkAccess
   *   Whether to check current user access.
   *
   * @return array
   *   Of available transitions.
   */
  public static function getAvailableTransitions(array $element, $checkAccess = TRUE) {
    if (!isset($element['#workflow'])) {
      return [];
    }
    $webform = isset($element['#webform']) ? Webform::load($element['#webform']) : NULL;
    $account = User::load(\Drupal::currentUser()->id());
    /** @var LnCWorkflowsManager $workflowsManager */
    $workflowsManager = \Drupal::service('ln_campaign_workflows_element.manager');
    // If no state is set, assume the initial state:
    $initial_state = $workflowsManager->getInitialStateForElement($element) ? $workflowsManager->getInitialStateForElement($element)->id() : NULL;
    $state_is_set = isset($element['#value']['workflow_state']) && $element['#value']['workflow_state'] && $element['#value']['workflow_state'] != '';
    if ($state_is_set) {
      $current_state = $element['#value']['workflow_state'];
    } else {
      $current_state = $initial_state;
    }
    if(!$current_state){
      return [];
    }
    $workflow_id = $element['#workflow'];
    return $workflowsManager->getAvailableTransitionsForWorkflow($workflow_id, $current_state, $checkAccess ? $account : NULL, $webform);
  }

  /**
   * Convert transitions to options for a select.
   *
   * @param array $transitions
   *   Transitions to convert.
   *
   * @return array
   *   options keyed by id
   */
  public static function convertTransitionsToOptions(array $transitions) {
    $options = [];
    foreach ($transitions as $transition) {
      $options[$transition->id()] = $transition->label();
    }
    return $options;
  }
}

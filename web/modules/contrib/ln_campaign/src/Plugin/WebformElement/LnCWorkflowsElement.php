<?php

namespace Drupal\ln_campaign\Plugin\WebformElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\ln_campaign\LnCampaignConstants;
use Drupal\ln_campaign\Service\LnCWorkflowsManager;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\workflows\Entity\Workflow;
use Drupal\webform\Plugin\WebformElement\WebformCompositeBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'LnCWorkflowsElement' element.
 *
 * @WebformElement(
 *   id = "ln_campaign_workflows_element",
 *   label = @Translation("Campaign workflows element"),
 *   description = @Translation("Provides a Campaing workflows element."),
 *   category = @Translation("Workflow"),
 *   multiple = FALSE,
 *   composite = TRUE,
 *   states_wrapper = TRUE,
 *   default_key = "workflow",
 * )
 *
 * @see \Drupal\webform\Plugin\WebformElementInterface
 * @see \Drupal\webform\Annotation\WebformElement
 */
class LnCWorkflowsElement extends WebformCompositeBase {

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
   * Format a workflows element value.
   *
   * @param array $element
   *   Workflows element of a submission.
   * @param WebformSubmissionInterface $webform_submission
   *   The submission.
   * @param array $options
   *   An array of options.
   *
   * @return array
   *   Array of text lines.
   */
  protected function formatValue(array $element, WebformSubmissionInterface $webform_submission, array $options = []) {
    $value = $this->getValue($element, $webform_submission, $options);
    $lines = [];
    if (!empty($value['workflow_state'])) {
      /** @var \Drupal\workflows\StateInterface $state */
      $state = $this->workflowsManager->getStateFromElementAndId($element, $value['workflow_state']);
      if ($state) {
        $lines['workflow_state'] = $state ? $state->label() : $this->t('No workflow state');
        if (!empty($value['workflow_state_previous'])) {
          /** @var \Drupal\workflows\StateInterface $state */
          $previousState = $this->workflowsManager->getStateFromElementAndId($element, $value['workflow_state_previous']);
          $lines['workflow_state_previous'] = $previousState && $state->id() != $previousState->id() ? ' (previous: ' . $previousState->label() . ')' : '';
        }
      }
    }

    return $lines;
  }

  /**
   * {@inheritdoc}
   */
  protected function formatHtmlItemValue(array $element, WebformSubmissionInterface $webform_submission, array $options = []) {
    $lines = $this->formatValue($element, $webform_submission, $options);
    return $lines;
  }

  /**
   * {@inheritdoc}
   */
  protected function formatTextItem(array $element, WebformSubmissionInterface $webform_submission, array $options = []) {
    $lines = $this->formatValue($element, $webform_submission, $options);
    return implode(' ', $lines);
  }

  /**
   * {@inheritdoc}
   */
  protected function defineDefaultProperties() {
    $properties = parent::defineDefaultProperties();
    $properties['multiple'] = FALSE;
    // Core settings:
    $properties['workflow'] = '';
    $properties['require_transition_if_available'] = FALSE;
    $properties['hide_if_no_transitions'] = FALSE;
    $properties['show_workflow_form_on_view'] = TRUE;
    // Default to not showing on create form:
    $properties['access_view_workflow_enabled'] = TRUE;
    $properties['access_create_roles'] = [];
    $properties['access_create_workflow_enabled'] = FALSE;
    $properties['access_update_roles'] = ['authenticated'];
    $properties['access_update_workflow_enabled'] = TRUE;
    $transitions = static::getAllWorkflowTransitions();
    foreach ($transitions as $transition) {
      $properties['transition_' . $transition->id() . '_color'] = '';
      $properties['transition_' . $transition->id() . '_disabled_message'] = '';
      $properties['access_transition_' . $transition->id() . '_workflow_enabled'] = TRUE;
      $properties['access_transition_' . $transition->id() . '_roles'] = [];
      $properties['access_transition_' . $transition->id() . '_users'] = [];
      $properties['access_transition_' . $transition->id() . '_permissions'] = [];
      $properties['access_transition_' . $transition->id() . '_group_roles'] = [];
    }
    $states = static::getAllWorkflowStates();
    foreach ($states as $state) {
      $properties['state_' . $state->id() . '_color'] = '';
      $properties['state_' . $state->id() . '_allow_resubmission_transition'] = NULL;
    }
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    unset($form['composite']['element']);
    unset($form['composite']['flexbox']);
    $form['composite'] = [
      'workflow' => [
        '#type' => 'select',
        '#title' => $this->t('Workflow'),
        '#description' => $this->t('Please select a workflow. <a href=":href">Manage workflows here</a>.', [
          ':href' => Url::fromRoute('entity.workflow.collection')->toString(),
        ]),
        '#options' => $this->getWorkflowOptions(),
        '#required' => TRUE,
      ],
    ] + $form['composite'];
    $form['composite'] = $form['composite'] +
      [
        'require_transition_if_available' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Require user select a transition if one available'),
        ],
      ];
    $form['composite'] = $form['composite'] +
      [
        'hide_if_no_transitions' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Hide element if no transitions available for the user'),
        ],
      ];
    $form['composite'] = $form['composite'] +
      [
        'show_workflow_form_on_view' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Allow changing the workflow from the "view submission" page'),
          '#description' => $this->t('Provides a shortcut to updating the workflow without needing to go to the edit submission form.'),
        ],
      ];
    // Add transition and state options:
    if ($form_state->get('element_properties')['workflow'] != '') {
      $workflow = Workflow::load($form_state->get('element_properties')['workflow']);
      $transitions = $workflow->getTypePlugin()->getTransitions();
      // Conditional setup - allows user to disable certain states with conditional logic
      $conditional_states = &$form['conditional_logic']['states']['#state_options'];
      $optgroup = (string) $this->t('Workflow transitions');
      $conditional_states[$optgroup] = [];
      $enabledStates = ['access_view', 'access_update', 'access_create'];
      foreach ($enabledStates as $state) {
        $form['access'][$state][$state . '_workflow_enabled'] = [
          '#type' => 'checkbox',
          '#title' => t('Enable access to workflow element (subject to other access requirements below)'),
          '#description' => t('Uncheck this box to prevent any element access for this action.'),
          '#weight' => -100,
        ];
      }
      foreach ($transitions as $transition) {
        $id = 'transition_' . $transition->id();
        // Add transition settings box:
        $from = $transition->from();
        $from_labels = [];
        foreach ($from as $state) {
          $from_labels[] = $state->label();
        }
        $form['composite'][$id] = [
          '#type' => 'details',
          '#title' => t('Transition: @label', ['@label' => $transition->label()])
        ];
        // Settings:
        $form['composite'][$id][$id . '_disabled_message'] = [
          '#type' => 'textarea',
          '#rows' => 2,
          '#title' => $this->t('Message if disabled by a conditional'),
          '#description' => $this->t('If you disable transitions on the Conditional tab, this will be displayed to explain to the user why they might not be able to select a transition.'),
          '#required' => FALSE,
        ];
        // Conditional - add a 'disable' state for each transition
        $conditional_states[$optgroup]['disable_transition-' . $transition->id()] = 'Disable "' . $transition->label() . '" transition';
        $transition_access_id = 'access_transition_' . $transition->id();
        $form['access'][$transition_access_id] = $form['access']['access_update'];
        $form['access'][$transition_access_id]['#title'] = $this->t('Workflow transition "@label"', ['@label' => $transition->label()]);
        $form['access'][$transition_access_id]['#description'] = $this->t('Select roles and users that should be able to use transition "@label" to change submission status to @state', [
          '@label' => $transition->label(),
          '@state' => $transition->to()->label(),
        ]);
        $permissions_properties = ['workflow_enabled', 'roles', 'users', 'permissions', 'group_roles', 'group_membership_record'];
        foreach ($permissions_properties as $property) {
          if (isset($form['access']['access_update']['access_update_' . $property])) {
            $form['access'][$transition_access_id][$transition_access_id . '_' . $property] = $form['access']['access_update']['access_update_' . $property];
            unset($form['access'][$transition_access_id]['access_update_' . $property]);
          }
          if ($property == 'workflow_enabled') {
            $default_properties[$transition_access_id . '_' . $property] = TRUE;
          } else {
            $default_properties[$transition_access_id . '_' . $property] = [];
          }
        }
      }
    }

    return $form;
  }

  /**
   * Get the webform workflows available.
   */
  public static function getWorkflowOptions() {
    $options = [];
    $workflows = Workflow::loadMultipleByType(LnCampaignConstants::LN_CAMPAING_WORKFLOWS_ELEMENT);
    foreach ($workflows as $workflow) {
      $options[$workflow->id()] = t('@label', ['@label' => $workflow->label()]);
    }
    ksort($options);

    return $options;
  }

  /**
   * Get all transitions available for all available workflows.
   */
  public static function getAllWorkflowTransitions() {
    $options = [];
    $workflows = Workflow::loadMultipleByType(LnCampaignConstants::LN_CAMPAING_WORKFLOWS_ELEMENT);
    foreach ($workflows as $workflow) {
      $workflowsManager = \Drupal::service('ln_campaign_workflows_element.manager');
      $workflowType = $workflowsManager->getWorkflowType($workflow->id());
      $options = array_merge($options, $workflowType->getTransitions());
    }

    return $options;
  }

  /**
   * Get all states available for all available workflows.
   */
  public static function getAllWorkflowStates() {
    $options = [];
    $workflows = Workflow::loadMultipleByType(LnCampaignConstants::LN_CAMPAING_WORKFLOWS_ELEMENT);
    foreach ($workflows as $workflow) {
      $workflowsManager = \Drupal::service('ln_campaign_workflows_element.manager');
      $workflowType = $workflowsManager->getWorkflowType($workflow->id());
      $options = array_merge($options, $workflowType->getStates());
    }

    return $options;
  }
}

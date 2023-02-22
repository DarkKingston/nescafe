<?php

namespace Drupal\ln_campaign\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\ln_campaign\Service\LnCWorkflowsManager;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\workflows\Entity\Workflow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Webform submission action handler.
 *
 * @WebformHandler(
 *   id = "ln_campaign_workflows_transition_email",
 *   label = @Translation("E-mail on workflow state change"),
 *   category = @Translation("Notification"),
 *   description = @Translation("Sends an email when a submission status changes."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 *   tokens = TRUE,
 * )
 */
class LnCStateChangeEmailWebformHandler extends LnCEmailWebformHandler {

  /**
   * @var LnCWorkflowsManager
   */
  protected $workflowsManager;

  /**
   * @var AccountProxyInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->workflowsManager = $container->get('ln_campaign_workflows_element.manager');
    $instance->currentUser = $container->get('current_user');

    return $instance;
  }

  /**
   * Get configuration default values.
   *
   * @return array
   *   Configuration default values.
   */
  protected function getDefaultConfigurationValues() {
    $this->defaultValues = parent::getDefaultConfigurationValues();
    $this->defaultValues['states'] = [];
    return $this->defaultValues;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $options = [];
    // Load all available transitions on the form per workflow element
    $workflow_elements = $this->workflowsManager->getWorkflowElementsForWebform($this->webform);
    foreach ($workflow_elements as $element_id => $element) {
      $transitions = $this->workflowsManager->getTransitionsForWorkflow($element['#workflow']);
      foreach ($transitions as $transition) {
        $options[$element_id . ':' . $transition->id()] = $this->t('…when submission transitions through <b>"@label"</b> to <b>"@state"</b>. <i>[@element]</i>', [
          '@label' => $transition->label(),
          '@state' => $transition->to()->label(),
          '@element' => $element['#title'],
        ]);
      }
    }
    $form['additional']['states']['#options'] = $options;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    if (!isset($this->configuration['states'])) {
      return FALSE;
    }
    $workflow_elements = $this->workflowsManager->getWorkflowElementsForWebform($this->webform);
    foreach ($workflow_elements as $element_id => $element) {
      $data = $webform_submission->getElementData($element_id);
      $message = $this->getMessage($webform_submission);
      // Send e-mail if running a transition:
      if (isset($data['transition']) && $data['transition'] != '' && in_array($element_id . ':' . $data['transition'], $this->configuration['states'])) {
        $originalState = $data['workflow_state_previous'];
        $changedState = $data['workflow_state'];
        if ($originalState != $changedState) {
          $this->sendMessage($webform_submission, $message);
        }
      }
    }
    if (isset($data['transition'])) {
      \Drupal::logger('ln_campaign')->notice('Sending e-mail for workflow transition ' . $data['transition']);
    }
  }

  public function preSave(WebformSubmissionInterface $webform_submission) {
    parent::preSave($webform_submission);
    $webform = $webform_submission->getWebform();
    $workflow_elements = $this->workflowsManager->getWorkflowElementsForWebform($webform);
    foreach ($workflow_elements as $element_id => $element) {
      $data = $webform_submission->getElementData($element_id);
      $newData = $data;
      $workflow = Workflow::load($element['#workflow']);
      if (!$workflow) {
        continue;
      }
      $workflowType = $this->workflowsManager->getWorkflowType($element['#workflow']);
      if (!$workflowType) {
        continue;
      }
      if (!$data) {
        $initialState = $workflowType->getInitialState();
        if ($initialState) {
          $newData['workflow_state'] = $initialState->id();
          $newData['workflow_state_label'] = $initialState->label();
          $newData['workflow_state_markup'] = $initialState->label();
        } else {
          continue;
        }
      }
      if ($data && isset($data['transition']) && $data['transition'] != '') {
        $transition = $workflowType->getTransition($data['transition']);
        if (!$transition) {
          $newData['transition'] = '';
          $webform_submission->setElementData($element_id, $newData);
          continue;
        }
        // Confirm access to submission and transition
        $account = $this->currentUser;
        $access = $this->workflowsManager->checkAccessForSubmissionAndTransition($workflow, $account, $webform_submission->getWebform(), $transition);
        if (!$access) {
          $newData['transition'] = '';
          $webform_submission->setElementData($element_id, $newData);
          continue;
        }
        // Set workflow state field value to the value of the new transition
        $newData['workflow_state'] = $transition->to()->id();
        $newData['workflow_state_label'] = $transition->to()->label();
        // Log webform submissions to the 'webform_submission' log.
        if ($webform->hasSubmissionLog()) {
          $context = [
            'link' => ($webform_submission->id()) ? $webform_submission->toLink(t('Edit'), 'edit-form')->toString() : NULL,
            'webform_submission' => $webform_submission,
            'operation' => 'workflow status changed',
            '@title' => $element['#title'],
            '@transition' => $transition->label(),
            '@state_old' => $data['workflow_state'],
            '@state_new' => $transition->to()->label(),
            '@user_submitted' => '',
            '@user_on_behalf_of' => '',
            '@transition_id' => t('Technical reference: [workflow:@element_id:@workflow_plugin_id:@transition_id:@state_id]', [
              '@element_id' => $element_id,
              '@workflow_plugin_id' => $workflowType->getPluginId(),
              '@transition_id' => $transition->id(),
              '@state_id' => $transition->to()->id(),
            ]),
          ];
          $message = '@title: transition "@transition" - status changed from "@state_old" to "@state_new". <br><br>@transition_id';
          \Drupal::logger('webform_submission')->notice($message, $context);
        }
      }
      // workflow_state_previous is set by default on the form
      $webform_submission->setElementData($element_id, $newData);
    }
  }

}

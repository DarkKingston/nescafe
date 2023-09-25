<?php

namespace Drupal\ln_campaign\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\ln_campaign\Service\LnCWorkflowsManager;
use Drupal\webform\Entity\Webform;
use Drupal\webform\WebformInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the webform submission filter form.
 */
class LnCWebformSubmissionFilterForm extends FormBase {

  /**
   * @var LnCWorkflowsManager
   */
  protected $workflowsManager;

  /**
   * The current route match.
   *
   * @var RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * @param LnCWorkflowsManager $workflowsManager
   * @param RouteMatchInterface $routeMatch
   */
  public function __construct(LnCWorkflowsManager $workflowsManager, RouteMatchInterface $routeMatch) {
    $this->workflowsManager = $workflowsManager;
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ln_campaign_workflows_element.manager'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ln_campaign_webform_submission_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $search = NULL, $source_entity = NULL, $source_entity_options = []) {
    $form['#attributes'] = ['class' => ['webform-filter-form']];
    $form['filter'] = [
      '#type' => 'details',
      '#title' => $this->t('Filter submissions'),
      '#open' => TRUE,
      '#attributes' => ['class' => ['container-inline']],
    ];
    $form['filter']['search'] = [
      '#type' => 'search',
      '#title' => $this->t('Keyword'),
      '#title_display' => 'invisible',
      '#placeholder' => $this->t('Filter by submitted data and/or notes'),
      '#maxlength' => 128,
      '#size' => 40,
      '#default_value' => $search,
    ];
    if ($source_entity_options) {
      if ($source_entity_options instanceof WebformInterface) {
        $form['filter']['entity'] = [
          '#type' => 'search',
          '#title' => $this->t('Campaign'),
          '#title_display' => 'invisible',
          '#autocomplete_route_name' => 'entity.webform.results.source_entity.autocomplete',
          '#autocomplete_route_parameters' => ['webform' => $source_entity_options->id()],
          '#placeholder' => $this->t('Enter submitted to…'),
          '#size' => 20,
          '#default_value' => $source_entity,
        ];
      }
      else {
        $form['filter']['entity'] = [
          '#type' => 'select',
          '#title' => $this->t('Submitted to'),
          '#title_display' => 'invisible',
          '#options' => ['' => $this->t('Filter by Campaign')] + $source_entity_options,
          '#default_value' => $source_entity,
        ];
      }
    }
    $form['filter']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Filter'),
      '#weight' => 200,
    ];
    if (!empty($search)) {
      $form['filter']['reset'] = [
        '#type' => 'submit',
        '#submit' => ['::resetForm'],
        '#value' => $this->t('Reset'),
        '#weight' => 200,
      ];
    }
    $webform = NULL;
    $webform_id = $this->routeMatch->getRawParameter('webform'); // There must be a better way to get the webform...
    if ($webform_id) {
      $webform = Webform::load($webform_id);
    }
    if (!$webform) {
      return $form;
    }
    $workflow_elements = $this->workflowsManager->getWorkflowElementsForWebform($webform);
    $form_state->set('workflow_elements', $workflow_elements);
    foreach ($workflow_elements as $element_id => $element) {
      $workflowType = $this->workflowsManager->getWorkflowType($element['#workflow']);
      $states = $workflowType->getStates();
      $options = ['' => $this->t('Filter by Status')];
      foreach ($states as $state) {
        $options[$state->id()] = $state->label();
      }
      $form['filter']['workflow-' . $element_id] = [
        '#type'          => 'select',
        '#title'         => t($element['#title']),
        '#title_display' => 'invisible',
        '#options'       => $options,
        '#default_value' => \Drupal::request()->query->get('workflow-' . $element_id),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $query = [
      'search' => trim($form_state->getValue('search')),
      'entity' => trim($form_state->getValue('entity')),
    ];
    foreach ($form_state->get('workflow_elements') as $element_id => $element) {
      if ($form_state->getValue('workflow-' . $element_id)) {
        $query['workflow-' . $element_id] = $form_state->getValue('workflow-' . $element_id);
      }
    }
    $form_state->setRedirect($this->getRouteMatch()->getRouteName(), $this->getRouteMatch()->getRawParameters()->all(), [
      'query' => $query ,
    ]);
  }

  /**
   * Resets the filter selection.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function resetForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect($this->getRouteMatch()->getRouteName(), $this->getRouteMatch()->getRawParameters()->all());
  }


}

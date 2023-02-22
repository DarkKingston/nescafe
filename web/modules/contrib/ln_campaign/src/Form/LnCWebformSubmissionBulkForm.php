<?php

namespace Drupal\ln_campaign\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the webform submission bulk form.
 */
class LnCWebformSubmissionBulkForm extends FormBase {

  /**
   * Can user delete any submission.
   *
   * @var bool
   */
  protected $submissionDeleteAny = FALSE;

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Class constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ln_campaign_webform_submission_bulk_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $table=NULL, $submission_delete_any = FALSE) {
    if(!$table){
      return [];
    }
    $this->submissionDeleteAny = $submission_delete_any;
    foreach ($table['#rows'] as $key => $row) {
      $table['#rows'][$key] = $row['data'] + ['#attributes' => ['data-webform-href' => $row['data-webform-href']]];
    }
    $form['#attributes']['class'][] = 'webform-bulk-form';
    // Operations.
    $form['operations'] = [
      '#prefix' => '<div class="container-inline">',
      '#suffix' => '</div>',
    ];
    $form['operations']['action'] = [
      '#type' => 'select',
      '#title' => $this->t('Action'),
      '#title_display' => 'invisible',
      '#options' => $this->getBulkOptions(),
      '#empty_option' => $this->t('- Select operation -'),
    ];
    $form['operations']['apply_above'] = [
      '#type' => 'submit',
      '#value' => $this->t('Apply to selected items'),
    ];
    // Table select.
    $form['items'] = $table;
    $form['items']['#type'] = 'tableselect';
    $form['items']['#options'] = $table['#rows'];
    $form['apply_below'] = [
      '#type' => 'submit',
      '#value' => $this->t('Apply to selected items'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $action = $form_state->getValue('action');
    if (empty($action)) {
      $form_state->setErrorByName(NULL, $this->t('No operation selected.'));
    }
    $entity_ids = array_filter($form_state->getValue('items'));
    if (empty($entity_ids)) {
      $form_state->setErrorByName(NULL, $this->t('No items selected.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $actions = $this->getActions();
    if (!isset($actions[$form_state->getValue('action')])) {
      return;
    }
    $action = $actions[$form_state->getValue('action')];
    $entity_ids = array_filter($form_state->getValue('items'));
    $entities = $this->entityTypeManager->getStorage('webform_submission')->loadMultiple($entity_ids);
    foreach ($entities as $key => $entity) {
      // Skip execution if the user did not have access.
      if (!$action->getPlugin()->access($entity, $this->currentUser())) {
        $this->messenger()->addError($this->t('No access to execute %action on the @entity_type_label %entity_label.', [
          '%action' => $action->label(),
          '@entity_type_label' => $entity->getEntityType()->getLabel(),
          '%entity_label' => $entity->label(),
        ]));
        unset($entities[$key]);
        continue;
      }
    }
    $count = count($entities);
    // If there were entities selected but the action isn't allowed on any of
    // them, we don't need to do anything further.
    if (!$count) {
      return;
    }
    $action->execute($entities);
    $operation_definition = $action->getPluginDefinition();
    if (!empty($operation_definition['confirm_form_route_name'])) {
      $options = [
        'query' => $this->getDestinationArray(),
      ];
      $form_state->setRedirect($operation_definition['confirm_form_route_name'], [], $options);
    }
  }


  /**
   * Returns the available operations for this form.
   *
   * @return array
   *   An associative array of operations, suitable for a select element.
   */
  protected function getBulkOptions() {
    $actions = $this->getActions();
    $options = [];
    foreach ($actions as $id => $action) {
      $options[$id] = $action->label();
    }
    return $options;
  }

  /**
   * Get the entity type's actions.
   *
   * @return \Drupal\system\ActionConfigEntityInterface[]
   *   An associative array of actions.
   */
  protected function getActions() {
    if (!isset($this->actions)) {
      $this->actions = $this->entityTypeManager->getStorage('action')->loadByProperties(['type' => 'webform_submission']);
      unset($this->actions['webform_submission_make_lock_action']);
      unset($this->actions['webform_submission_make_sticky_action']);
      unset($this->actions['webform_submission_make_unlock_action']);
      unset($this->actions['webform_submission_make_unsticky_action']);
      if (!$this->submissionDeleteAny) {
        unset($this->actions['webform_submission_delete_action']);
      }
    }
    return $this->actions;
  }

}

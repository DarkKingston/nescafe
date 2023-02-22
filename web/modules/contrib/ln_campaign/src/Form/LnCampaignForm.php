<?php

namespace Drupal\ln_campaign\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for the campaign entity edit forms.
 */
class LnCampaignForm extends ContentEntityForm {

  /**
   * The Current User object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a NodeForm object.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(EntityRepositoryInterface $entity_repository, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL, AccountInterface $current_user, DateFormatterInterface $date_formatter) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
    $this->currentUser = $current_user;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('current_user'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\ln_campaign\Entity\LnCampaign $entity */
    $entity = $this->entity;

    $form['#theme'] = ['ln_campaign_edit_form'];

    $form['changed'] = [
      '#type' => 'hidden',
      '#default_value' => $entity->getChangedTime(),
    ];

    $form = parent::form($form, $form_state);

    $form['revision_information']['#type'] = 'container';
    $form['revision_information']['#group'] = 'meta';

    $form['advanced']['#type'] = 'container';
    $form['advanced']['#attributes']['class'][] = 'entity-info';

    $form['meta'] = [
      '#type' => 'container',
      '#group' => 'advanced',
      '#weight' => -10,
      '#title' => $this->t('Status'),
      '#attributes' => ['class' => ['entity-meta__header']],
      '#tree' => TRUE,
    ];

    $form['meta']['changed'] = [
      '#type' => 'item',
      '#title' => $this->t('Last saved'),
      '#markup' => !$entity->isNew() ? $this->dateFormatter->format($entity->getChangedTime(), 'short') : $this->t('Not saved yet'),
      '#wrapper_attributes' => ['class' => ['entity-meta__last-saved', 'container-inline']],
      '#weight' => 10,
    ];
    $form['meta']['author'] = [
      '#type' => 'item',
      '#title' => $this->t('Author'),
      '#markup' => $entity->getOwner()->getAccountName(),
      '#wrapper_attributes' => ['class' => ['entity-meta__author', 'container-inline']],
      '#weight' => 11,
    ];

    $form['status']['#group'] = 'footer';

    // Entity author information for administrators.
    $form['author'] = [
      '#type' => 'details',
      '#title' => $this->t('Authoring information'),
      '#group' => 'advanced',
      '#attributes' => [
        'class' => ['node-form-author'],
      ],
      '#attached' => [
        'library' => ['node/drupal.node'],
      ],
      '#weight' => 90,
      '#optional' => TRUE,
    ];

    if (isset($form['uid'])) {
      $form['uid']['#group'] = 'author';
    }

    if (isset($form['created'])) {
      $form['created']['#group'] = 'author';
    }



    $form['#attached']['library'][] = 'seven/node-form';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $entity = $this->getEntity();
    $result = $entity->save();
    $link = $entity->toLink($this->t('View'))->toRenderable();

    $message_arguments = ['%label' => $this->entity->label()];
    $logger_arguments = $message_arguments + ['link' => render($link)];

    if ($result == SAVED_NEW) {
      $this->messenger()->addStatus($this->t('New campaign %label has been created.', $message_arguments));
      $this->logger('ln_campaign')->notice('Created new campaign %label', $logger_arguments);
    }
    else {
      $this->messenger()->addStatus($this->t('The campaign %label has been updated.', $message_arguments));
      $this->logger('ln_campaign')->notice('Updated new campaign %label.', $logger_arguments);
    }

    $form_state->setRedirect('entity.ln_campaign.canonical', ['ln_campaign' => $entity->id()]);
  }

}

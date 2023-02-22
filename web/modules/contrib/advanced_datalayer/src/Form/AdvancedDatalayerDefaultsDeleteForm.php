<?php

namespace Drupal\advanced_datalayer\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\advanced_datalayer\AdvancedDatalayerManager;

/**
 * Builds the form to delete Datalayer defaults entities.
 */
class AdvancedDatalayerDefaultsDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Global and entity defaults can't be deleted.
    $entity = $form_state->getFormObject()->getEntity();
    if (in_array($entity->id(), AdvancedDatalayerManager::protectedDefaults(), TRUE)) {
      return [
        '#type' => 'item',
        '#markup' => $this->t("You can't delete Global or entity defaults!"),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete %name?', ['%name' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.advanced_datalayer_defaults.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();

    $this->messenger()->addMessage(
      $this->t('Deleted @label defaults.',
        [
          '@label' => $this->entity->label(),
        ]
      )
    );

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}

<?php

namespace Drupal\ln_seo_hreflang\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the hreflang entity edit forms.
 */
class LnHreflangForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $entity = $this->getEntity();
    $result = $entity->save();

    $message_arguments = ['%label' => $this->entity->label()];

    if ($result == SAVED_NEW) {
      $this->messenger()->addStatus($this->t('New hreflang %label has been created.', $message_arguments));
      $this->logger('ln_seo_hreflang')->notice('Created new hreflang %label', $message_arguments);
    }
    else {
      $this->messenger()->addStatus($this->t('The hreflang %label has been updated.', $message_arguments));
      $this->logger('ln_seo_hreflang')->notice('Updated new hreflang %label.', $message_arguments);
    }

    $form_state->setRedirect('entity.ln_hreflang.collection');
  }

}

<?php

namespace Drupal\ln_srh_full\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ln_srh\Form\SRHFieldMapping;
use Drupal\ln_srh\SRHConstants;


class SRHComplementFieldMapping extends SRHFieldMapping {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ln_srh_complement_mapping';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $bundle = NULL) {
    return parent::buildForm($form, $form_state, SRHConstants::SRH_COMPLEMENT_BUNDLE);
  }
}

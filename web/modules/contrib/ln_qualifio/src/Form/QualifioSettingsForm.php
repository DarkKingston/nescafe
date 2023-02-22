<?php

namespace Drupal\ln_qualifio\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for ln_qualifio.
 */
class QualifioSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ln_qualifio_settings_form';

  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['ln_qualifio.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ln_qualifio.settings');

    $form['campaigns_feed'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Redirect URL'),
      '#description' => $this->t('You can specify the campaigns feed URL.'),
      '#default_value' => $config->get('campaigns_feed'),
      '#attributes' => ['placeholder' => ['Enter campaigns feed url']],
      '#required' => TRUE,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('ln_qualifio.settings')
      ->set('campaigns_feed', trim($form_state->getValue('campaigns_feed')))
      ->save();
    parent::submitForm($form, $form_state);
  }

}

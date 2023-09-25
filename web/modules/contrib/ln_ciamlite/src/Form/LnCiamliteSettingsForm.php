<?php

namespace Drupal\ln_ciamlite\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class LnCiamliteSettingsForm extends ConfigFormBase{

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ln_ciamlite.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ln_ciamlite_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('ln_ciamlite.settings');
    $form['gigya'] = [
      '#type' => 'container',
      '#title' => $this->t('Gigya'),
      '#tree' => TRUE,
    ];
    $form['gigya']['screen_set'] = [
      '#type' => 'textfield',
      '#title'=> $this->t('Gigya Screenset id'),
      '#default_value' => $config->get('gigya.screen_set'),
      '#required' => TRUE,
    ];
    $form['gigya']['mobile_screen_set'] = [
      '#type' => 'textfield',
      '#title'=> $this->t('Gigya Mobile Screenset id'),
      '#default_value' =>  $config->get('gigya.mobile_screen_set'),
      '#required' => TRUE,
    ];
    $form['gigya']['start_screen'] = [
      '#type' => 'textfield',
      '#title'=> $this->t('Gigya start Screen id'),
      '#default_value' => $config->get('gigya.start_screen'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('ln_ciamlite.settings');
    $config->set('gigya', $form_state->getValue('gigya'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }
}

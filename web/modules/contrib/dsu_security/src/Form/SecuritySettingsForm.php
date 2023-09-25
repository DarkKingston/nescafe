<?php

namespace Drupal\dsu_security\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for dsu_security.
 */
class SecuritySettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dsu_security_settings_form';

  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['dsu_security.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('dsu_security.settings');

    $form['redirect_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Redirect URL'),
      '#description' => $this->t('You can specify the URL where user will be redirected after registration.'),
      '#default_value' => $config->get('redirect_url'),
      '#attributes' => ['placeholder' => ['home']],
      '#required' => TRUE,
      '#size' => 50,
      '#maxlength' => 15,
    ];
    $form['override_cookies_lifetime'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Override Cookie Lifetime'),
      '#description' => $this->t('You can override drupal session cookie lifetime value (In Seconds).'),
      '#default_value' => (!empty($config->get('override_cookies_lifetime')) || $config->get('override_cookies_lifetime') == '0') ? $config->get('override_cookies_lifetime') : 0,
      '#attributes' => ['placeholder' => ['Cookies lifetime (In Secs)']],
      '#required' => TRUE,
      '#size' => 50,
      '#maxlength' => 15,
    ];
    $form['jquery_patch'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include patch for jQuery 2.2.4'),
      '#description' => $this->t('Include jQuery (2.2.4) from the DSU security module version and apply the patch.'),
      '#default_value' => $config->get('jquery_patch'),
    ];
    $form['disable_edit_email'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable edit email field'),
      '#description' => $this->t('Disable the edition of the email field in the user\'s profile.'),
      '#default_value' => $config->get('disable_edit_email'),
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
    $this->config('dsu_security.settings')
      ->set('redirect_url', $form_state->getValue('redirect_url'))
      ->set('override_cookies_lifetime', $form_state->getValue('override_cookies_lifetime'))
      ->set('jquery_patch', $form_state->getValue('jquery_patch'))
      ->set('disable_edit_email', $form_state->getValue('disable_edit_email'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}

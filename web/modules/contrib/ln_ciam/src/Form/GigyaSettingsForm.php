<?php

namespace Drupal\ln_ciam\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the Gigya settings form.
 */
class GigyaSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ln_ciam_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ln_ciam.settings',
    ];
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ln_ciam.settings');
    $form['hint_text'] = [
      '#type' => 'markup',
      '#title' => 'Description',
      '#markup' => $this->t("1. Generate the encryption key in your project <a href ='https://developers.gigya.com/display/GD/Drupal+8' target ='blank'>Reference link</a>.
	  OR use <b>'php docroot/modules/contrib/ln_ciam/gigya/gigya/cli/encryption.php -gen abc > gigya.key'</b> command on terminal. <br>
	  2. Enter the key path in field."),
    ];
    $form['gigya_key_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Gigya key Path'),
      '#attributes' => ['placeholder' => '/var/www/drupal/docroot/gigya.key'],
      '#default_value' => $config->get('gigya_key_path'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::submitForm().
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    // Update the path to the encryption key in gigya module.
    \Drupal::configFactory()->getEditable('gigya.global')->set('gigya.keyPath', $values['gigya_key_path'])->save();

    $this->configFactory->getEditable('ln_ciam.settings')
      ->set('gigya_key_path', $values['gigya_key_path'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}

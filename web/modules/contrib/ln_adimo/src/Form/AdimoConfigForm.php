<?php

namespace Drupal\ln_adimo\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the Adimo configuration form.
 */
class AdimoConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ln_adimo_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ln_adimo.settings',
    ];
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ln_adimo.settings');
    $form['seckit_default_value'] = [
      '#type' => 'textarea',
      '#title' => 'Seckit config',
      '#required' => TRUE,
      '#default_value' => !empty($config->get('seckit_default_value')) ? $config->get('seckit_default_value') : '',
      '#description' => $this->t("Enter seckit config values separated by space. Ex : https://*.adimo.co https://*.adimouat.co https://4dvq37jqcg.execute-api.eu-west-1.amazonaws.com_"),
    ];
    $form['previous_default_value'] = [
      '#type' => 'hidden',
      '#title' => 'Seckit Previous config',
      '#default_value' => $config->get('seckit_default_value'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::submitForm().
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Form state values.
    $values = $form_state->getValues();
    // New Field value.
    $new_val = trim($values['seckit_default_value']);
    // Previous config save value.
    $existing_val = trim($values['previous_default_value']);
    // save new value in ln_adimo config.
    $this->configFactory->getEditable('ln_adimo.settings')
      ->set('seckit_default_value', $values['seckit_default_value'])
      ->save();
    parent::submitForm($form, $form_state);
    // Load seckit config.
    $existing_seckit_config = $this->configFactory->getEditable('seckit.settings');
    // Load adimo config.
    $adimo_config = $this->config('ln_adimo.settings');
    $get_script_data = $existing_seckit_config->get(seckit_xss);
    $adimo_default_config = $adimo_config->get('seckit_default_value');
    // Field array to write config values.
    $field_arr = ['script-src', 'style-src', 'img-src', 'frame-src', 'font-src', 'connect-src'];
    $get_script_val = '';
    $unique_config_val = '';
    $config = $this->configFactory->getEditable('seckit.settings');
    // convert existing value into array for diff.
    $explode_existingval = explode(" ", $existing_val);
    foreach ($field_arr as $field_val) {
      $get_script_val = $get_script_data['csp'][$field_val];
      $explode_scriptval = explode(" ", $get_script_data['csp'][$field_val]);
      $unique_config_val = array_diff($explode_scriptval, $explode_existingval);
      $unique_config_val_implode = implode(" ", $unique_config_val);
      $get_script_val = $unique_config_val_implode.' '.$new_val;
      $config->set('seckit_xss.csp.'.$field_val, $get_script_val);
    }
    drupal_flush_all_caches();
    // Assign values in seckit config.
    $existing_seckit_config->save(TRUE);
  }

}

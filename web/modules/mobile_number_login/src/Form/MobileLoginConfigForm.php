<?php

namespace Drupal\mobile_number_login\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * LanguageConverter Admin Form.
 */
class MobileLoginConfigForm extends ConfigFormBase {

  /**
   * Settings Config Variable.
   *
   * @var DrupalSettings
   */
  protected $settings;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->settings = 'mobilenumberlogin.settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mobile_login_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      $this->settings,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config($this->settings);
    $form['default_countries'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Default Countries'),
      '#default_value' => $config->get('default_countries'),
      '#description' => $this->t('Add the values as comma seperated. EX: us, in ... ') . Link::fromTextAndUrl($this->t('Check country list'), Url::fromUri('https://www.nationsonline.org/oneworld/country_code_list.htm'))->toString() . $this->t(' Choose "Alpha 2" from here'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config($this->settings)
      ->set('default_countries', $form_state->getValue('default_countries'))
      ->save();
    return parent::submitForm($form, $form_state);
  }

}

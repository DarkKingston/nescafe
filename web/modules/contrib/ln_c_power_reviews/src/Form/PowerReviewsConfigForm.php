<?php

namespace Drupal\ln_c_power_reviews\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class PowerReviewsConfigForm.
 *
 * @package Drupal\ln_c_power_reviews\Form
 */
class PowerReviewsConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ln_c_power_reviews_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ln_c_power_reviews.settings'];
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $ln_c_power_reviews_settings = $this->config('ln_c_power_reviews.settings');

    $form['general'] = [
      '#type'        => 'details',
      '#open'        => TRUE,
      '#title'       => $this->t('PowerReviews keys'),
      '#description' => $this->t('Define keys used in the PowerReviews output. Keys for field values are configurable via the field edit form.'),
    ];

    // Build form elements.
    $pr_api_key_read = $ln_c_power_reviews_settings->get('pr_api_key_read');
    $form['general']['ln_pr_api_key_read'] = [
      '#type'          => 'textfield',
      '#required'      => TRUE,
      '#title'         => $this->t('PowerReviews API Read Key (Display)'),
      '#description'   => $this->t('Enter the value for the PowerReviews API Read Key assigned to this site'),
      '#default_value' => isset($pr_api_key_read) ? $pr_api_key_read : '',
      '#size'          => 100,
    ];

    $pr_api_key_write = $ln_c_power_reviews_settings->get('pr_api_key_write');
    $form['general']['ln_pr_api_key_write'] = [
      '#type'          => 'textfield',
      '#required'      => TRUE,
      '#title'         => $this->t('PowerReviews API Write Key (Write)'),
      '#description'   => $this->t('Enter the value for the PowerReviews API Write Key assigned to this site'),
      '#default_value' => isset($pr_api_key_write) ? $pr_api_key_write : '',
      '#size'          => 100,
    ];

    $pr_locale = $ln_c_power_reviews_settings->get('pr_locale');
    $form['general']['ln_pr_locale'] = [
      '#type'          => 'textfield',
      '#required'      => TRUE,
      '#title'         => $this->t('PowerReviews Locale'),
      '#description'   => $this->t('Enter the language/country code for use in the PowerReviews localization widget'),
      '#default_value' => isset($pr_locale) ? $pr_locale : 'en_US',
      '#size'          => 30,
    ];

    $pr_merchant_group_id = $ln_c_power_reviews_settings->get('pr_merchant_group_id');
    $form['general']['ln_pr_merchant_group_id'] = [
      '#type'          => 'textfield',
      '#required'      => TRUE,
      '#title'         => $this->t('PowerReviews Merchant Group ID'),
      '#description'   => $this->t('Enter the value for the PowerReviews Merchant Group ID assigned to this site'),
      '#default_value' => isset($pr_merchant_group_id) ? $pr_merchant_group_id : '',
      '#size'          => 30,
    ];

    $pr_merchant_id = $ln_c_power_reviews_settings->get('pr_merchant_id');
    $form['general']['ln_pr_merchant_id'] = [
      '#type'          => 'textfield',
      '#required'      => TRUE,
      '#title'         => $this->t('PowerReviews Merchant ID'),
      '#description'   => $this->t('Enter the value for the PowerReviews Merchant ID to be assigned to this site'),
      '#default_value' => isset($pr_merchant_id) ? $pr_merchant_id : '',
      '#size'          => 30,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save default form settings.
    $this->config('ln_c_power_reviews.settings')
      ->set('pr_api_key_read', $form_state->getValue('ln_pr_api_key_read'))
      ->set('pr_api_key_write', $form_state->getValue('ln_pr_api_key_write'))
      ->set('pr_locale', $form_state->getValue('ln_pr_locale'))
      ->set('pr_merchant_group_id', $form_state->getValue('ln_pr_merchant_group_id'))
      ->set('pr_merchant_id', $form_state->getValue('ln_pr_merchant_id'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}

<?php

namespace Drupal\ln_price_spider\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class PriceSpiderConfigForm.
 *
 * @package Drupal\ln_price_spider\Form
 */
class PriceSpiderConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ln_price_spider_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ln_price_spider.settings'];
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $price_spider_settings = $this->config('ln_price_spider.settings');
    // Get current language of site.
    $price_spider_site_lang = \Drupal::service('ln_price_spider')
      ->getPriceSpiderLangCode();

    // Get country code based on language.
    $price_spider_country = \Drupal::service('ln_price_spider')
      ->getPriceSpiderCountryCode($price_spider_site_lang);

    $form['general'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Price Spider keys'),
      '#description' => $this->t('Define keys used in the Price Spider output. Keys for field values are configurable via the field edit form.'),
    ];

    // Build form elements.
    $ps_key = $price_spider_settings->get('ps-key');
    $form['general']['ps_key'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Price Spider Key'),
      '#description' => $this->t('The value for Price spider following Site specific standard'),
      '#default_value' => isset($ps_key) ? $ps_key : '3344-5cb4a424ad45c0000cfdfa9e',
      '#size' => 30,
    ];

    $ps_country = $price_spider_settings->get('ps-country');
    $form['general']['ps_country'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Country'),
      '#description' => $this->t('Country or Site specific zone.'),
      '#default_value' => isset($ps_country) ? $ps_country : $price_spider_country,
      '#size' => 30,
    ];

    $ps_language = $price_spider_settings->get('ps-language');
    $form['general']['ps_language'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Language key'),
      '#description' => $this->t('Language of website eg en, de.'),
      '#default_value' => isset($ps_language) ? $ps_language : $price_spider_site_lang,
      '#size' => 30,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save default form settings.
    $this->config('ln_price_spider.settings')
      ->set('ps-key', $form_state->getValue('ps_key'))
      ->set('ps-country', $form_state->getValue('ps_country'))
      ->set('ps-language', $form_state->getValue('ps_language'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}

<?php

namespace Drupal\ln_contenthub\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the Content Hub settings form.
 */
class ContentHubSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ln_contenthub_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ln_contenthub.settings',
    ];
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ln_contenthub.settings');
    $form['#tree'] = TRUE;

    $form['general'] = [
      '#type' => 'details',
      '#title' => $this->t('Common Configuration'),
      '#open' => TRUE,
    ];

    $form['filter'] = [
      '#type' => 'details',
      '#title' => $this->t('Common Filter'),
      '#description' => $this->t('The value of all the filter fields below will be used in each query for the Content Hub.
      <br /> <br /><b> Note: </b> Please note that in order for the automatic update cron to work you will need to at least add one value in the filters below.'),
      '#open' => TRUE,
    ];

    $form['brand_filter'] = [
      '#type' => 'details',
      '#title' => $this->t('Brand Filter'),
      '#description' => $this->t('Show and hide brand filters on all content hub entity browsers from the configuration..'),
      '#open' => TRUE,
    ];

    $form['cron'] = [
      '#type' => 'details',
      '#title' => $this->t('Cron configuration'),
      '#open' => TRUE,
    ];

    $form['general']['server_uri'] = [
      '#type' => 'url',
      '#title' => $this->t('Server url'),
      '#description' => $this->t('Including trailing slash. Ie: http://www.example.org/'),
      '#default_value' => $config->get('ln_contenthub_server_uri'),
      '#required' => TRUE,
    ];

    $form['general']['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API key'),
      '#description' => $this->t("Get in touch with your MSE contact in case you don't know how to obtain an API key."),
      '#default_value' => $config->get('ln_contenthub_api_key'),
      '#required' => TRUE,
    ];

    $form['filter']['brand_range'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Brand range'),
      '#description' => $this->t("Enter brand range value (eg. nestlé nespresso)."),
      '#default_value' => $config->get('ln_contenthub_brand_range'),
    ];

    $form['filter']['brand_corporate'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Brand corporate'),
      '#description' => $this->t("Enter brand range corporate (eg. starbucks)."),
      '#default_value' => $config->get('ln_contenthub_brand_corporate'),
    ];

    $form['filter']['applicable_region'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Applicable region'),
      '#description' => $this->t("Enter applicable region value (eg. nestlé nespresso)."),
      '#default_value' => $config->get('ln_contenthub_applicable_region'),
    ];

    $form['filter']['creator_region'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Creator region'),
      '#description' => $this->t("Enter creator region value (eg. Nestlé Beverage)."),
      '#default_value' => $config->get('ln_contenthub_creator_region'),
    ];
	
	  $form['brand_filter']['sub_brand'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show sub brand filter on contenthub entity browser.'),
      '#default_value' => $config->get('ln_contenthub_sub_brand'),
      '#required' => FALSE,
    ];
	
	  $form['brand_filter']['product_category'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show product category filter on contenthub entity browser.'),
      '#default_value' => $config->get('ln_contenthub_product_category'),
      '#required' => FALSE,
    ];

    $form['cron']['ipr'] = [
      '#type' => 'radios',
      '#title' => $this->t('Delete images'),
      '#options' => [
        'delete_assets' => $this->t('Delete images with Intellectual Property Rights expired.'),
        'disable_assets' => $this->t('Disable(unpublish) images with Intellectual Property Rights expired.'),
        'none' => $this->t('Nothing')
      ],
      '#default_value' => $config->get('ln_contenthub_delete_expired'),
      '#required' => TRUE,
    ];
	
	  $form['cron']['cron_interval'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Set the interval time to synchronize media with the Content Hub'),
      '#options'       => [
        '43200'   => $this->t('Every 12 hours'),
        '86400'   => $this->t('Every day'),
        '172800'  => $this->t('Every 2 days'),
        '259200'  => $this->t('Every 3 days'),
        '432000'  => $this->t('Every 5 days'),
        '604800'  => $this->t('Every 7 days'),
        '1296000' => $this->t('Every 15 days'),
        '2592000' => $this->t('Every 30 days'),
      ],
      '#default_value' => $config->get('ln_contenthub_media_sync_interval'),
      '#description'   => $this->t('Interval value indicates the period in which synchronization process is enqueued in drupal cron jobs.'),
    ];


    return parent::buildForm($form, $form_state);
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::submitForm().
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->configFactory->getEditable('ln_contenthub.settings')
      ->set('ln_contenthub_server_uri', $values['general']['server_uri'])
      ->set('ln_contenthub_api_key', $values['general']['api_key'])
      ->set('ln_contenthub_delete_expired', $values['cron']['ipr'])
      ->set('ln_contenthub_sub_brand', $values['brand_filter']['sub_brand'])
      ->set('ln_contenthub_product_category', $values['brand_filter']['product_category'])
      ->set('ln_contenthub_brand_range', $values['filter']['brand_range'])
      ->set('ln_contenthub_brand_corporate', $values['filter']['brand_corporate'])
      ->set('ln_contenthub_applicable_region', $values['filter']['applicable_region'])
      ->set('ln_contenthub_creator_region', $values['filter']['creator_region'])
	  ->set('ln_contenthub_media_sync_interval', $values['cron']['cron_interval'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}

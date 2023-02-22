<?php

namespace Drupal\ln_pdh\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ln_pdh\PdhConnectorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PdhSettingsForm. Config for the Pdh module.
 *
 * @package Drupal\ln_pdh\Form
 */
class PdhSettingsForm extends ConfigFormBase {

  /**
   * The PDH connector.
   *
   * @var \Drupal\ln_pdh\PdhConnectorInterface
   */
  protected $connector;

  /**
   * PdhSettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\ln_pdh\PdhConnectorInterface $pdh_connector
   *   The PDH connector service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, PdhConnectorInterface $pdh_connector) {
    parent::__construct($config_factory);
    $this->connector = $pdh_connector;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('ln_pdh.connector')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ln_pdh_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ln_pdh.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->checkConnection();

    $config = $this->config('ln_pdh.settings');

    $form['#tree'] = TRUE;

    $form['auth'] = [
      '#type' => 'details',
      '#title' => $this->t('Configure the PDH server authentication'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];

    $form['auth']['endpoint_url'] = [
      '#type' => 'url',
      '#title' => $this->t('PDH Endpoint URL'),
      '#description' => $this->t('Enter the PDH endpoint URL'),
      '#default_value' => $config->get('auth.endpoint_url'),
      '#required' => TRUE,
    ];

    $form['auth']['client'] = [
      '#type' => 'textfield',
      '#title' => $this->t('PDH Client'),
      '#description' => $this->t('Enter the PDH Client'),
      '#default_value' => $config->get('auth.client'),
      '#required' => TRUE,
    ];

    $form['auth']['country'] = [
      '#type' => 'textfield',
      '#title' => $this->t('PDH Country'),
      '#description' => $this->t('Enter the PDH Country'),
      '#default_value' => $config->get('auth.country'),
      '#required' => FALSE,
    ];

    $form['auth']['certificate_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The path and file name where the certificates are stored'),
      '#description' => $this->t('The PDH certificate path and file name without extension. It has to be a relative to the Drupal root path, like "sites/default/files/certs/cert_name".'),
      '#default_value' => $config->get('auth.certificate_path'),
      '#required' => TRUE,
    ];

    $form['auth']['passphrase'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The passphrase for the certificate'),
      '#description' => $this->t('The PDH certificate passphrase.'),
      '#default_value' => $config->get('auth.passphrase'),
      '#required' => FALSE,
    ];

    $form['auth']['brand_id'] = [
      '#type' => 'textfield',
      '#title'         => $this->t('Target brand ID'),
      '#description'   => $this->t('Enter the target brand ID'),
      '#default_value' => $config->get('auth.brand_id'),
      '#required'      => FALSE,
    ];

    $form['auth']['langcode'] = [
      '#type' => 'textfield',
      '#title'         => $this->t('Langcode to process'),
      '#description'   => $this->t('Enter the langcode in format xx_XX (e.g., pt-BR, pt-PT)'),
      '#default_value' => $config->get('auth.langcode'),
      '#required'      => TRUE,
    ];

    $form['sync'] = [
      '#type' => 'details',
      '#title' => $this->t('Configure synchronization process in your website'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];

    $form['sync']['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Turn on the schedule synchronization'),
      '#description' => $this->t('Be Aware! When using for the first time, the process may hang up due to memory shortage. Check you server configuration to avoid PHP limits'),
      '#return_value' => TRUE,
      '#default_value' => $config->get('sync.status'),

    ];

    $form['sync']['cron_interval'] = [
      '#type' => 'select',
      '#title' => $this->t('Set the interval time to Synchronize Products with PDH'),
      '#options' => [
        '10800' => $this->t('Every 3 hours'),
        '43200' => $this->t('Every 12 hours'),
        '86400' => $this->t('Every day'),
        '172800' => $this->t('Every 2 days'),
        '259200' => $this->t('Every 3 days'),
        '432000' => $this->t('Every 5 days'),
        '604800' => $this->t('Every 7 days'),
        '1296000' => $this->t('Every 15 days'),
        '2592000' => $this->t('Every 30 days'),
      ],
      '#default_value' => $config->get('sync.cron_interval'),
      '#description' => $this->t('Interval value indicates the period in which synchronization process is enqueued in drupal cron jobs'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate that the certificate files exist.
    $certificate_path = $form_state->getValue(['auth', 'certificate_path']);
    $certificate_filename = basename($certificate_path);

    if (!file_exists(DRUPAL_ROOT . '/' . $certificate_path . DIRECTORY_SEPARATOR . $certificate_filename . '.crt') ||
      !file_exists(DRUPAL_ROOT . '/' . $certificate_path . DIRECTORY_SEPARATOR . $certificate_filename . '.key')) {
      $form_state->setError($form['auth']['certificate_path'], $this->t('PDH certificate path is not valid'));
    }

    // Check that the langcode is one the allowed.
    $langcode = $form_state->getValue(['auth', 'langcode']);
    $regexp = '/^([a-z]{2}_[A-Z]{2})$/';
    if (filter_var($langcode, FILTER_VALIDATE_REGEXP,
      ['options' => ['regexp' => $regexp]]) === FALSE) {
      $form_state->setError($form['auth']['langcode'], $this->t('Langcode is not valid. Please use the format xx_XX (example: fr_FR)'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $sections = ['auth', 'sync'];
    $config = $this->config('ln_pdh.settings');

    foreach ($sections as $section) {
      $values = array_map('trim', $form_state->getValue($section));
      $config->set($section, $values);
    }
    $config->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Checks connection to PDH status and shows a message to inform.
   */
  protected function checkConnection() {
    if ($this->getRequest()->getMethod() == 'GET') {
      if ($this->connector->testConnection()) {
        $this->messenger()->addStatus($this->t('Connection to PDH successful.'));
      }
      else {
        $this->messenger()->addError($this->t('Unable to connect to PDH. Please check your credentials.'));
      }
    }
  }

}

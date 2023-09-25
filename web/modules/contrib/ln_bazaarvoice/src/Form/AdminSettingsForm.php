<?php

namespace Drupal\ln_bazaarvoice\Form;

use Drupal;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ln_bazaarvoice\LnBazaarvoiceConstants;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\ln_bazaarvoice\Service\LnBazaarvoiceService;
use Drupal\Core\Url;

/**
 * Class AdminSettingsForm.
 *
 * @package Drupal\ln_bazaarvoice\Form
 */
class AdminSettingsForm extends ConfigFormBase {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The bazaarvoice utils service.
   *
   * @var \Drupal\ln_bazaarvoice\Service\LnBazaarvoiceService
   */
  protected $bazaarvoice_utils;


  /**
   * Constructs a AdminSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\ln_bazaarvoice\Service\LnBazaarvoiceService $bazaarvoice_utils
   *   The bazaarvoice utils service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LanguageManagerInterface $language_manager, LnBazaarvoiceService $bazaarvoice_utils) {
    parent::__construct($config_factory);
    $this->languageManager = $language_manager;
    $this->bazaarvoice_utils = $bazaarvoice_utils;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('language_manager'),
      $container->get('ln_bazaarvoice.utils')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ln_bazaarvoice_admin_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ln_bazaarvoice.settings');
    $form['environment'] = [
      '#type' => 'select',
      '#title' => $this->t('Environment'),
      '#description' => $this->t('Mode to use for connecting to Bazaarvoice. Use staging for pre-production development and testing.'),
      '#options' => [
        LnBazaarvoiceConstants::ENVIRONMENT_STAG  => $this->t('Staging'),
        LnBazaarvoiceConstants::ENVIRONMENT_PRO => $this->t('Production'),
      ],
      '#default_value' => $config->get('environment'),
    ];


    $form['client_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client Name'),
      '#required' => TRUE,
      '#description' => $this->t('The client name provided by Bazaarvoice. Remember that this value is case sensitive.'),
      '#default_value' => $config->get('client_name'),
    ];

    $form['site_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Site Id'),
      '#required' => TRUE,
      '#description' => $this->t('The ID of the deployment zone you want to use. This is set in the Bazaarvoice configuration hub within the Bazaarvoice workbench.'),
      '#default_value' => $config->get('site_id'),
    ];


    $form['locale'] = [
      '#type' => 'table',
      '#caption' => [
        '#type' => 'html_tag',
        '#tag' => 'h5',
        '#value' => $this->t('Language mapping'),
      ],
      '#header'  => [
        $this->t('Drupal language'),
        $this->t('Bazaarvoice locale code'),
      ],
      '#tree'    => TRUE,
    ];

    foreach ($this->languageManager->getLanguages() as $language) {
      $form['locale'][$language->getId()]['drupal'] = [
        '#markup' => $language->getName(),
      ];
      $form['locale'][$language->getId()]['bazaarvoice'] = [
        '#title' => $this->t('Bazaarvoice locale'),
        '#title_display' => 'invisible',
        '#type' => 'textfield',
        '#maxlength' => 6,
        '#size' => 6,
        '#required' => TRUE,
        '#pattern' => '[a-z]{2}_[A-Z]{2}',
        '#description' => $this->t('Format: xx_YY'),
        '#default_value' => $config->get('locale')[$language->getId()] ?? '',
      ];
    }

    if(!empty($config->get('locale'))){
      $form['test_link'] = [
        '#title' => $this->t('Use this link to test if the current configuration works correctly, you should access the javascript file'),
        '#type' => 'link',
        '#url' => Url::fromUri($this->bazaarvoice_utils->getBazaarvoiceJsPath()),
        '#attributes' => ['target' => '_blank'],
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $locales = [];
    foreach($values['locale'] as $drupal_langcode => $code){
      $locales[$drupal_langcode] = $code['bazaarvoice'];
    }

    $this->config('ln_bazaarvoice.settings')
      ->set('environment', $values['environment'])
      ->set('client_name', $values['client_name'])
      ->set('site_id', $values['site_id'])
      ->set('locale', $locales)
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ln_bazaarvoice.settings'];
  }

}

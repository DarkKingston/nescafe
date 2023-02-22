<?php

namespace Drupal\ln_campaign\Plugin\DsField\ln_campaign;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\ds\Plugin\DsField\DsFieldBase;
use Drupal\ln_campaign\LnCampaignConstants;
use Drupal\ln_campaign\Service\LnCampaignHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Plugin that renders the 'start button'
 *
 * @DsField(
 *   id = "ln_campaign_webform",
 *   title = @Translation("Form"),
 *   provider = "ln_campaign",
 *   entity_type = "ln_campaign",
 * )
 */
class LnCWebform extends DsFieldBase {

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var LnCampaignHelper
   */
  protected $lnCampaignHelper;

  /**
   * @var AccountProxyInterface
   */
  protected $currentUser;

  /**
   * @var Request
   */
  protected $request;

  /**
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param EntityTypeManagerInterface $entityTypeManager
   * @param LnCampaignHelper $lnCampaignHelper
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, LnCampaignHelper $lnCampaignHelper, AccountProxyInterface $currentUser, Request $request) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->lnCampaignHelper = $lnCampaignHelper;
    $this->currentUser = $currentUser;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('ln_campaign.helper'),
      $container->get('current_user'),
      $container->get('request_stack')->getCurrentRequest()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $lnCampign = $this->entity();
    if($this->lnCampaignHelper->checkTimeOutPromotion($lnCampign)){
      $build = [
        '#theme' => 'ln_campaign_time_out',
        '#title' => $config['time_out']['title'],
        '#message' => $config['time_out']['message'],
      ];
    }else{
      $webform_id = $config['webform_id'] ?? '';
      if($config['authentication']['require_login'] == TRUE && $this->currentUser->isAnonymous()){
        $build = [
          '#theme' => 'ln_campaign_authentication_link',
          '#title' => $config['authentication']['link_title'],
          '#url' => Url::fromUserInput($config['authentication']['link_url'],['query' => ['destination' => $this->request->getRequestUri()]])->toString(),
          '#message' => $config['authentication']['message'],
        ];
      }else{
        $build = [
          '#type' => 'webform',
          '#webform' => $webform_id,
          '#default_data' => ['ln_campaign_id' => $this->entity()->id(), 'ln_campaign_name' => $this->entity()->label()],
        ];
      }
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $form = parent::settingsForm($form, $form_state);
    $webforms = $this->entityTypeManager->getStorage('webform')->loadByProperties(['category' => LnCampaignConstants::LN_CAMPAING_WORKFLOWS_CATEGORY]);
    $options = [];
    foreach ($webforms as $webform){
      $options[$webform->id()] = $webform->label();
    }
    $form['webform_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Webform'),
      '#options' => $options,
      '#default_value' => $config['webform_id'],
      '#required' => TRUE,
    ];
    $form['time_out'] = [
      '#type' => 'details',
      '#title' => $this->t('Time out Settings'),
      '#tree' => TRUE,
    ];
    $form['time_out']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $config['time_out']['title'],
    ];
    $form['time_out']['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#default_value' => $config['time_out']['message'],
    ];
    $form['authentication'] = [
      '#type' => 'details',
      '#title' => $this->t('Authentication Settings'),
      '#tree' => TRUE,
    ];
    $form['authentication']['require_login'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('require_login'),
      '#default_value' => $config['authentication']['require_login'],
    ];
    $form['authentication']['link_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link title'),
      '#default_value' => $config['authentication']['link_title'],
      '#states' => [
        'visible' => [
          ':input[name="fields[ln_campaign_webform][settings_edit_form][settings][authentication][require_login]"]' => ['checked' => TRUE],
        ],
        'required' => [
          ':input[name="fields[ln_campaign_webform][settings_edit_form][settings][authentication][require_login]"]' => ['checked' => TRUE],
        ]
      ]
    ];
    $form['authentication']['link_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Link url'),
      '#description' => $this->t('It must be an internal url and start with "/".'),
      '#element_validate' => [[static::class, 'validateUriElement']],
      '#default_value' => $config['authentication']['link_url'],
      '#states' => [
        'visible' => [
          ':input[name="fields[ln_campaign_webform][settings_edit_form][settings][authentication][require_login]"]' => ['checked' => TRUE],
        ],
        'required' => [
          ':input[name="fields[ln_campaign_webform][settings_edit_form][settings][authentication][require_login]"]' => ['checked' => TRUE],
        ]
      ]
    ];
    $form['authentication']['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Authentication message'),
      '#default_value' => $config['authentication']['message'],
      '#states' => [
        'visible' => [
          ':input[name="fields[ln_campaign_webform][settings_edit_form][settings][authentication][require_login]"]' => ['checked' => TRUE],
        ],
      ]
    ];
    return $form;
  }
  /**
   * {@inheritdoc}
   */
  public function settingsSummary($settings) {
    $config = $this->getConfiguration();
    $summary = [];
    $summary[] = 'Webform: ' . $config['webform_id'];
    $summary[] = 'Time out(title): ' . $config['time_out']['title'];
    $summary[] = 'Time out(message): ' . $config['time_out']['message'];
    if($config['authentication']['require_login'] == TRUE){
      $summary[] = 'Authentication:';
      $summary[] = 'require login: ' . $config['authentication']['require_login'];
      $summary[] = 'link title: ' . $config['authentication']['link_title'];
      $summary[] = 'link url: ' . $config['authentication']['link_url'];
      $summary[] = 'message: ' . $config['authentication']['message'];
    }
    return $summary;
  }
  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $configuration = [
      'webform_id' => '',
      'time_out' => [
        'title' => $this->t('Campaign out of date'),
        'message' => '',
      ],
      'authentication' => [
        'link_title' => $this->t('Login/Register'),
        'link_url' => Url::fromRoute('user.login')->toString(),
        'message' => '',
        'require_login' => FALSE,
      ],

    ];
    return $configuration;
  }

  /**
   * Form element validation handler for the 'url' element.
   *
   */
  public static function validateUriElement($element, FormStateInterface $form_state, $form) {
    $uri = $element['#value'];
    $form_state->setValueForElement($element, $uri);
    if ($element['#value'][0] != '/') {
      $form_state->setError($element, t('Manually entered paths should start with one of the following characters: / ? #'));
    }
  }


}

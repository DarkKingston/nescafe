<?php

namespace Drupal\ln_srh\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\Language;
use Drupal\ln_srh\SRHConstants;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\user\Entity\User;

class SRHConnectionSettings extends ConfigFormBase{

  /** @var string Config settings */
  const SETTINGS = 'ln_srh.settings';

  /**
   * Drupal\Core\Language\LanguageManager definition.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * Drupal\Core\Messenger\Messenger definition.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected ModuleHandlerInterface $moduleHandler;

  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(LanguageManager $languageManager, EntityTypeManagerInterface $entityTypeManager, ModuleHandlerInterface $moduleHandler, Messenger $messenger) {
    $this->languageManager = $languageManager;
    $this->messenger = $messenger;
    $this->entityTypeManager = $entityTypeManager;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('language_manager'),
      $container->get('entity_type.manager'),
      $container->get('module_handler'),
      $container->get('messenger')
    );
  }

  public function getFormId() {
    return 'ln_srh_settings';
  }

  protected function srhComplementsBundleExists() {
    if ($this->moduleHandler->moduleExists('ln_srh_full')) {
      $complementBundle = $this->entityTypeManager->getStorage('node_type')
        ->load(SRHConstants::SRH_COMPLEMENT_BUNDLE);
      if ($complementBundle) {
        return TRUE;
      }
    }
    return FALSE;
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    // Get the locales that user has set up.
    $locales = $config->get('locales');
    $default_author = User::load($config->get('author'));
    $complementsBundleExists = $this->srhComplementsBundleExists();

    if (empty($locales)) {
      // Init array to avoid Warnings.
      $locales = ['0' => ['connect_markets' => '', 'langcode' => '']];
    }

    $form['syncro_conf'] = [
      '#type'        => 'fieldset',
      '#title'       => $this->t('configure the synchronization parameters of the recipes'),
      '#collapsible' => TRUE,
      '#collapsed'   => FALSE,
    ];

    $form['syncro_conf']['author'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#title' => $this->t('Author'),
      '#description' => $this->t('Enter the username'),
      '#default_value' => $default_author,
      '#required' => TRUE,
    ];

    $form['syncro_conf']['enable_recipes_sync'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Active the recipe synchronization'),
      '#default_value' => $config->get('enable_recipes_sync'),
    ];

    if ($complementsBundleExists) {
      $form['syncro_conf']['enable_complements_sync'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Active the complement synchronization'),
        '#default_value' => $config->get('enable_complements_sync'),
      ];
    }

    $form['syncro_conf']["interval_time"] = [
      '#type' => 'number',
      '#min' => 1,
      '#title' => $this->t('Interval time'),
      '#default_value' => $config->get('interval_time'),
      '#description' => $this->t('Time interval to execute migration in seconds. Set to 28800, to be executed after every 8 hours.'),
      '#required' => TRUE,
    ];

    $form['syncro_conf']['pages_to_sync'] = [
      '#type' => 'number',
      '#title' => $this->t('Pages sync every time'),
      '#description' => $this->t('Pages to synchronize in each synchronization'),
      '#default_value' => $config->get('pages_to_sync'),
    ];

    $form['server_conf'] = [
      '#type'        => 'fieldset',
      '#title'       => $this->t('Configure the server call to Smart Recipe Hub'),
      '#collapsible' => TRUE,
      '#collapsed'   => FALSE,
    ];

    $form['server_conf']['url'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('URL'),
      '#description'   => $this->t('Set the URL of the SRH.'),
      '#default_value' => $config->get('url'),
      '#required'      => TRUE,
    ];

    $form['server_conf']['channel_id'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Channel ID'),
      '#description'   => $this->t('Set the Channel ID.'),
      '#default_value' => $config->get('channel_id'),
      '#required'      => TRUE,
    ];

    $form['server_conf']['apikey'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('API Key'),
      '#description'   => $this->t('Set the API KEY.'),
      '#default_value' => $config->get('apikey'),
      '#required'      => TRUE,
    ];

    $form['server_conf']['market_code'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('SRH Market Code'),
      '#description'   => $this->t('Set SRH Market code.'),
      '#default_value' => $config->get('market_code'),
      '#required'      => TRUE,
    ];

    $form['locales'] = [
      '#type'        => 'fieldset',
      '#title'       => $this->t('Configure the languages of the connector'),
      '#description' => $this->t('Allow to synchronize different languages.'),
      '#weight'      => 80,
      '#tree'        => TRUE,
      '#attributes' => [
        'id' => 'js-ajax-elements-wrapper-locale'
      ],
    ];
    if ($form_state->get('field_locales') == '') {
      $form_state->set('field_locales', range(0, count($locales) - 1));
    }
    $field_count_locales = $form_state->get('field_locales');
    foreach ($field_count_locales as $key => $delta) {
      $form['locales'][$delta] = [
        '#type'       => 'container',
        '#attributes' => [
          'class' => ['container-inline'],
        ],
        '#tree'       => TRUE,
      ];

      $form['locales'][$delta]['connect_markets'] = [
        '#type'          => 'textfield',
        '#title'         => $this->t('Set Market'),
        '#description'   => $this->t('Set the Market Code.'),
        '#default_value' => $locales[$key]['connect_markets'],
        '#required'      => FALSE,
        '#size'          => 15,
      ];

      $form['locales'][$delta]['content_prefix'] = [
        '#type'          => 'textfield',
        '#title'         => $this->t('Recipes prefix'),
        '#description'   => $this->t('Set the URL prefix for recipes.'),
        '#default_value' => $locales[$key]['content_prefix'] ?? '',
        '#required'      => FALSE,
        '#size'          => 30,
      ];

      // Add setting for complement prefix if complement content type exists.
      if ($complementsBundleExists) {
        $form['locales'][$delta]['complement_prefix'] = [
          '#type'          => 'textfield',
          '#title'         => $this->t('Complements prefix'),
          '#description'   => $this->t('Set the URL prefix for complements.'),
          '#default_value' => $locales[$key]['complement_prefix'] ?? '',
          '#required'      => FALSE,
          '#size'          => 30,
        ];
      }

      $form['locales'][$delta]['langcode'] = [
        '#title'         => $this->t('Language'),
        '#type'          => 'language_select',
        '#languages'     => Language::STATE_ALL,
        '#default_value' => $locales[$key]['langcode'],
      ];

      $form['locales'][$delta]['remove_locale'] = [
        '#type'       => 'submit',
        '#value'      => $this->t('-'),
        '#submit'     => ['::localeRemoveOne'],
        '#remove_locale' => TRUE,
        '#ajax'       => [
          'callback' => '::localeRemoveOneCallback',
          'wrapper'  => 'js-ajax-elements-wrapper-locale',
        ],
        '#weight'     => -50,
        '#attributes' => [
          'class' => ['button-small'],
        ],
        '#name'       => 'remove_name_' . $delta,
      ];
    }
    $form['locales']['add_locale'] = [
      '#type'   => 'submit',
      '#value'  => $this->t('Add more languages'),
      '#submit' => ['::localeAddOne'],
      '#add_locale' => TRUE,
      '#ajax'   => [
        'callback' => '::localeAddOneCallback',
        'wrapper'  => 'js-ajax-elements-wrapper-locale',
      ],
      '#weight' => 410,
    ];
    $form['reset_time_sync'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Reset last time to sync on save'),
    ];
    return parent::buildForm($form, $form_state);
  }

    public function validateForm(array &$form, FormStateInterface $form_state) {
    // Gestting the market values.
    $locales = $form_state->getValue('locales');
    $trigger = $form_state->getTriggeringElement();
    $markets = [];
    unset($locales['add_locale']);
    if (!empty($locales)) {
      foreach ($locales as $key => $value) {
        if ($value['connect_markets'] != '') {
          $markets[] = $value['connect_markets'];
        }
        if ($value['content_prefix'] != '' && !preg_match('/^\//i',$value['content_prefix'])){
          $form_state->setErrorByName('locales', $this->t('The content_prefix has to start with a slash.'));
        }
        if (isset($value['complement_prefix']) && $value['complement_prefix'] != '' && !preg_match('/^\//i',$value['complement_prefix'])){
          $form_state->setErrorByName('locales', $this->t('The complement_prefix has to start with a slash.'));
        }
      }
    }
    $add_locale = $trigger["#add_locale"] ?? FALSE;
    if ( !$add_locale && empty($markets) ){
      $form_state->setErrorByName('locales', $this->t('Missing Locale settings'));
      return;
    }

    if (!empty($markets)) {
      foreach ($markets as $market) {
        $this->checkRecipesMarket($market, $form_state);
      }
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * @param string $locale
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function checkRecipesMarket($locale, FormStateInterface $form_state) {
    $url = $form_state->getValue('url') . '/' . SRHConstants::SRH_API_VERSION;
    $apikey = $form_state->getValue('apikey');
    $channel_id = $form_state->getValue('channel_id');
    $market_code = $form_state->getValue('market_code');
    $trigger = $form_state->getTriggeringElement();

    /** @var \Drupal\ln_srh\Services\SRHInterface $connector */
    $connector = \Drupal::service('srh');
    $check_status = $connector->checkStatusSRH($url, $apikey, $channel_id, $market_code, $locale);

    if ( !($trigger["#add_locale"] ?? FALSE)  && !($trigger["#remove_locale"] ?? FALSE) ){
      if ($check_status['code'] == '200'){
        $this->messenger->addMessage($this->t('------------------ Market ' . $locale .
          ' report: --------------------'), 'status');

        // Correct HTTP Response, let's check Market and Tags.
        if ($check_status['message'] == '0'){
          $form_state->setErrorByName('connect_market', $this->t('No results obtained in: ' . $market_code .
            ', please check the Market Code'));
        }
        else {
          $this->messenger->addMessage($this->t('With the current SRH configuration ' . $check_status['message'] .
            ' recipes are going to be synchronized.'), 'status');
        }
      } else {
        if ($check_status['code'] == '403') {
          $form_state->setErrorByName('apikey', $this->t('Oops! Foorbiden acces! check if the APIKEY or URL VERSION are correct.
        -> The HTTP code and the reason was: ' . $check_status['message']));
          $form_state->setErrorByName('url');
        }
        else {
          $form_state->setErrorByName('url', $this->t('Networking error, check the URL. Error:' . $check_status['message']));
        }
      }


    }
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function localeAddOne(array &$form, FormStateInterface $form_state) {
    // Store our form state.
    $field_deltas_array = $form_state->get('field_locales');

    // Check to see if there is more than one item in our array.
    if (count($field_deltas_array) > 0) {
      // Add a new element to our array and set it to our highest value plus one.
      $field_deltas_array[] = max($field_deltas_array) + 1;
    }
    else {
      // Set the new array element to 0.
      $field_deltas_array[] = 0;
    }
    // Rebuild the field deltas values.
    $form_state->set('field_locales', $field_deltas_array);

    // Rebuild the form.
    $form_state->setRebuild();

    // Return any messages set.
    $this->messenger->messagesByType('status');
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return mixed
   */
  public function localeAddOneCallback(array &$form, FormStateInterface $form_state) {
    return $form['locales'];
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function localeRemoveOne(array &$form, FormStateInterface $form_state) {
    // Get the triggering item.
    $delta_remove = $form_state->getTriggeringElement()['#parents'][1];

    // Store our form state.
    $field_deltas_array = $form_state->get('field_locales');

    // Find the key of the item we need to remove.
    $key_to_remove = array_search($delta_remove, $field_deltas_array);

    // Remove our triggered element.
    unset($field_deltas_array[$key_to_remove]);

    // Rebuild the field deltas values.
    $form_state->set('field_locales', $field_deltas_array);

    // Rebuild the form.
    $form_state->setRebuild();

    // Return any messages set.
    $this->messenger->messagesByType('status');
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return mixed
   */
  public function localeRemoveOneCallback(array &$form, FormStateInterface $form_state) {
    return $form['locales'];
  }

  /**
   *
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable(static::SETTINGS);
    $valueLocales = ($form_state->getValue('locales'));
    unset($valueLocales['add_locale']);
    $locales = [];
    foreach ($valueLocales as $value) {
      if ($value['connect_markets'] != '') {
        $langcode = empty($value['langcode']) ? $this->languageManager->getCurrentLanguage()->getId() : $value['langcode'];
        $localeValue = [
          'connect_markets' => $value['connect_markets'],
          'content_prefix' => $value['content_prefix'],
          'langcode' => $langcode
        ];
        if (isset($value['complement_prefix'])) {
          $localeValue['complement_prefix'] = $value['complement_prefix'];
        }
        $locales[] = $localeValue;
      }
    }
    \Drupal::logger('SRH')
      ->notice('locales; <pre><code>' . print_r($locales, TRUE) . '</code></pre');

    $currentSettings = [
      'channel_id' => $config->get('channel_id'),
      'url' => $config->get('url'),
      'apikey' => $config->get('apikey'),
      'market_code' => $config->get('market_code'),
      'pages_to_sync' => $config->get('pages_to_sync'),
    ];

    $config->set('author', $form_state->getValue('author'));
    $config->set('url', $form_state->getValue('url'));
    $config->set('channel_id', $form_state->getValue('channel_id'));
    $config->set('apikey', $form_state->getValue('apikey'));
    $config->set('market_code', $form_state->getValue('market_code'));
    $config->set('locales', $locales);
    $config->set('enable_recipes_sync', $form_state->getValue('enable_recipes_sync'));
    if ($this->srhComplementsBundleExists()) {
      $config->set('enable_complements_sync', $form_state->getValue('enable_complements_sync'));
    }
    $config->set('interval_time', $form_state->getValue('interval_time'));
    $config->set('pages_to_sync', $form_state->getValue('pages_to_sync'));

    $config->save();

    // Reset locales sync time when check "Reset time sync" or change connection settings.
    if ($form_state->getValue('reset_time_sync')) {
      \Drupal::state()->deleteMultiple([
        'ln_srh_recipes_locales_sync',
        'ln_srh_complements_locales_sync',
      ]);
      \Drupal::logger('SRH')
        ->notice('Reset locales sync time');
    }
    else {
      foreach ($currentSettings as $key => $value) {
        if ($config->get($key) != $value) {
          \Drupal::state()->deleteMultiple([
            'ln_srh_recipes_locales_sync',
            'ln_srh_complements_locales_sync',
          ]);
          \Drupal::logger('SRH')
            ->notice('Reset locales sync time');
          break;
        }
      }
    }
    return parent::submitForm($form, $form_state);
  }
}

<?php

namespace Drupal\dsu_engage\Form;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dsu_engage\Controller\DsuEngageController;
use Egulias\EmailValidator\EmailValidator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\Core\Utility\LinkGeneratorInterface;

/**
 *
 */
class DsuEngageForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The email validator.
   *
   * @var \Egulias\EmailValidator\EmailValidator
   */
  protected $emailValidator;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var Drupal\Core\Utility\LinkGeneratorInterface
   */
  protected $link_generator;

  /**
   * Constructs a new FieldStorageAddForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EmailValidator $email_validator, EntityTypeManagerInterface $entity_type_manager, LinkGeneratorInterface $linkGenerator) {
    $this->configFactory = $config_factory;
    $this->emailValidator = $email_validator;
    $this->entityTypeManager = $entity_type_manager;
    $this->link_generator = $linkGenerator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('email.validator'),
      $container->get('entity_type.manager'),
      $container->get('link_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'dsu_engage.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dsu_engage_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // @todo Refactor since required option doesn't respect at all the backend settings.
    $fields = [
      0 => [
        'name' => 'field_product_description',
        'label' => 'Product Description',
      ],
      1 => ['name' => 'field_first_name', 'label' => 'First Name'],
      2 => ['name' => 'field_last_name', 'label' => 'Last Name'],
      3 => [
        'name' => 'field_preferred_channel',
        'label' => 'Preferred Channel',
      ],
      4 => ['name' => 'field_email', 'label' => 'E-Mail'],
      5 => ['name' => 'field_phone', 'label' => 'Phone'],
      6 => ['name' => 'field_phone_prefix', 'label' => 'Phone prefix'],
      7 => ['name' => 'field_batch_code', 'label' => 'Batch Code'],
      8 => ['name' => 'field_bar_code', 'label' => 'Bar Code'],
      9 => [
        'name' => 'field_best_before_date',
        'label' => 'Best before date',
      ],
      10 => ['name' => 'field_street', 'label' => 'Street'],
      11 => ['name' => 'field_city', 'label' => 'City'],
      12 => ['name' => 'field_zip_code', 'label' => 'Zip Code'],
      13 => ['name' => 'field_state', 'label' => 'State'],
      14 => ['name' => 'field_country', 'label' => 'Country'],
      15 => ['name' => 'field_attachments', 'label' => 'Attachments'],
    ];
    $info = \Drupal::service('extension.list.module')->getExtensionInfo('dsu_engage');
    $config = $this->configFactory->get('dsu_engage.settings');
    $form['#theme'] = 'dsu_engage_form';

    // Adding basic CSS to the form.
    $form['#attached']['library'][] = 'dsu_engage/dsu_engage_form';
    $form['#attached']['drupalSettings']['phone_prefix'] = DsuEngageController::getPhonePrefixes();
    $form['#attached']['drupalSettings']['dsu_engage']['data'] = [
      'module_name' => $info['name'],
      'module_version' => $info['version'],
    ];
    // Create buttons instead of radio selected.
    $selected = $config->get('show_field_request_type_options');

    // Create hidden radio buttons.
    $options = [];

    // User submit data.
    $user_data = $form_state->getUserInput();
    // The buttons.
    $question_buttons = "";
    $currentRequest = $form_state->getValue('request_type') ?: 'question';
    $activeQuestion = $currentRequest === 'question' ? 'active' : '';
    $activeComplaint = $currentRequest === 'complaint' ? 'active' : '';
    $activePraise = $currentRequest === 'praise' ? 'active' : '';
    $lang_id = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $tooltip_config = \Drupal::config('dsu_engage_tooltip_form.settings');

    if ($selected['question']) {
      $question_buttons .= '<a class="questionbuttons questionbutton1 col - md - 4 " data-eventlabel = ' . ucwords($selected['question']) . '>' . $this->t("I have a question/suggestion") . '</a>';
      $options['question'] = $this->t("I have a question/suggestion");
    }

    if ($selected['complaint']) {
      $question_buttons .= '<a class="questionbuttons questionbutton2 col - md - 4 "  data-eventlabel = ' . ucwords($selected['complaint']) . '>' . $this->t("I have a complaint") . '</a>';
      $options['complaint'] = $this->t("I have a complaint");
    }

    if ($selected['praise']) {
      $question_buttons .= '<a class="questionbuttons questionbutton3 col - md - 4 " data-eventlabel = ' . ucwords($selected['praise']) . '>' . $this->t("I have a praise") . '</a>';
      $options['praise'] = $this->t("I have a praise");
    }

    $form['contact_us_form'] = [
      '#type' => 'details',
      '#open' => 'open',
    ];

    $form['contact_us_form']['request_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('What would you like to contact us about?'),
      '#options' => $options,
      '#default_value' => $currentRequest,
      '#suffix' => $question_buttons,
      '#required' => TRUE,
    ];
    $form['contact_us_form']['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#title_display' => 'invisible',
      '#cols' => 10,
      '#attributes' => [
        'placeholder' => $this->t('Your message *'),
      ],
      '#filter' => [
        'strip_tags',
      ],
      '#rules' => [
        'length[40,32000]',
      ],
      '#maxlength' => 4096,
    ];

    if ($config->get('show_field_product_description_question') || $config->get('show_field_product_description_complaint') || $config->get('show_field_product_description_praise')
      || $config->get('show_field_bar_code_question') || $config->get('show_field_bar_code_complaint') || $config->get('show_field_bar_code_praise')
      || $config->get('show_field_batch_code_question') || $config->get('show_field_batch_code_complaint') || $config->get('show_field_batch_code_praise')
      || $config->get('show_field_best_before_date_question') || $config->get('show_field_best_before_date_complaint') || $config->get('show_field_best_before_date_praise')) {

      $visible = [];
      if ($config->get('show_field_product_description_question') || $config->get('show_field_bar_code_question') || $config->get('show_field_batch_code_question') || $config->get('show_field_best_before_date_question')) {
        $visible[] = [
          ':input[name="request_type"]' => ['value' => 'question'],
        ];
      }
      if ($config->get('show_field_product_description_complaint') || $config->get('show_field_bar_code_complaint') || $config->get('show_field_batch_code_complaint') || $config->get('show_field_best_before_date_complaint')) {
        $visible[] = [
          ':input[name="request_type"]' => ['value' => 'complaint'],
        ];
      }
      if ($config->get('show_field_product_description_praise') || $config->get('show_field_bar_code_praise') || $config->get('show_field_batch_code_praise') || $config->get('show_field_best_before_date_praise')) {
        $visible[] = [
          ':input[name="request_type"]' => ['value' => 'praise'],
        ];
      }
      $form['contact_us_form']['product'] = [
        '#type' => 'details',
        '#title' => $this->t('Product Information'),
        '#open' => TRUE,
        '#states' => [
          'visible' => $visible,
        ],
      ];
    }

    if ($config->get('show_field_product_description_question') || $config->get('show_field_product_description_complaint') || $config->get('show_field_product_description_praise')) {
      $visible = [];
      $required = [];

      if ($config->get('show_field_product_description_question')) {
        $visible[] = [
          ':input[name="request_type"]' => ['value' => 'question'],
        ];
        if ($config->get('mandatory_field_product_description_question')) {
          $required[':input[name="request_type"]'][] = ['value' => 'question'];
        }
      }
      if ($config->get('show_field_product_description_complaint')) {
        $visible[] = [
          ':input[name="request_type"]' => ['value' => 'complaint'],
        ];
        if ($config->get('mandatory_field_product_description_complaint')) {
          $required[':input[name="request_type"]'][] = ['value' => 'complaint'];
        }
      }
      if ($config->get('show_field_product_description_praise')) {
        $visible[] = [
          ':input[name="request_type"]' => ['value' => 'praise'],
        ];
        if ($config->get('mandatory_field_product_description_praise')) {
          $required[':input[name="request_type"]'][] = ['value' => 'praise'];
        }
      }
      $form['contact_us_form']['product']['product_description'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Product description'),
        '#title_display' => 'invisible',
        '#attributes' => [
          'placeholder' => $this->t('Product description'),
          'size' => '',
        ],
        '#states' => [
          'visible' => $visible,
          'required' => $required,
        ],
        '#rules' => [
          'length[3,255]',
        ],
      ];
    }

    if ($config->get('show_field_batch_code_question') || $config->get('show_field_batch_code_complaint') || $config->get('show_field_batch_code_praise')) {
      $visible = [];
      $required = [];

      if ($config->get('show_field_batch_code_question')) {
        $visible[] = [
          ':input[name="request_type"]' => ['value' => 'question'],
        ];
        if ($config->get('mandatory_field_batch_code_question')) {
          $required[':input[name="request_type"]'][] = ['value' => 'question'];
        }
      }
      if ($config->get('show_field_batch_code_complaint')) {
        $visible[] = [
          ':input[name="request_type"]' => ['value' => 'complaint'],
        ];
        if ($config->get('mandatory_field_batch_code_complaint')) {
          $required[':input[name="request_type"]'][] = ['value' => 'complaint'];
        }
      }
      if ($config->get('show_field_batch_code_praise')) {
        $visible[] = [
          ':input[name="request_type"]' => ['value' => 'praise'],
        ];
        if ($config->get('mandatory_field_batch_code_praise')) {
          $required[':input[name="request_type"]'][] = ['value' => 'praise'];
        }
      }

      $form['contact_us_form']['product']['batchcode'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Batch code'),
        '#title_display' => 'invisible',
        '#attributes' => [
          'placeholder' => $this->t('Batch code'),
          'data-tool-tip' => 'has-batch-code',
        ],
        '#states' => [
          'visible' => $visible,
          'required' => $required,
        ],
        '#maxlength' => 64,
      ];

      $tooltip_image_batch = $tooltip_config->get('dsu_engage_tooltip_image_batch_' . $lang_id);
      if (isset($tooltip_image_batch['0']) && !empty($tooltip_image_batch['0'])) {
        $form['contact_us_form']['tooltip_url_batch'] = [
          '#type' => 'markup',
          '#markup' => get_engage_file_url_using_fid($tooltip_image_batch['0']),
          '#prefix' => '<div class ="hide">',
          '#suffix' => '</div>',
        ];
      }

      $tooltip_text_batch = $tooltip_config->get('dsu_engage_tooltip_text_batch_code_' . $lang_id);
      if (isset($tooltip_text_batch) && !empty($tooltip_text_batch)) {
        $form['contact_us_form']['tooltip_text_batch'] = [
          '#type' => 'markup',
          '#markup' => $tooltip_text_batch,
          '#prefix' => '<div class ="hide">',
          '#suffix' => '</div>',
        ];
      }

    }
    if ($config->get('show_field_bar_code_question') || $config->get('show_field_bar_code_complaint') || $config->get('show_field_bar_code_praise')) {
      $visible = [];
      $required = [];

      if ($config->get('show_field_bar_code_question')) {
        $visible[] = [
          ':input[name="request_type"]' => ['value' => 'question'],
        ];
        if ($config->get('mandatory_field_bar_code_question')) {
          $required[':input[name="request_type"]'][] = ['value' => 'question'];
        }
      }

      if ($config->get('show_field_batch_code_complaint')) {
        $visible[] = [
          ':input[name="request_type"]' => ['value' => 'complaint'],
        ];
        if ($config->get('mandatory_field_bar_code_complaint')) {
          $required[':input[name="request_type"]'][] = ['value' => 'complaint'];
        }
      }

      if ($config->get('show_field_bar_code_praise')) {
        $visible[] = [
          ':input[name="request_type"]' => ['value' => 'praise'],
        ];
        if ($config->get('mandatory_field_bar_code_praise')) {
          $required[':input[name="request_type"]'][] = ['value' => 'praise'];
        }
      }

      $tooltip_image_bar = $tooltip_config->get('dsu_engage_tooltip_image_bar_' . $lang_id);
      if (isset($tooltip_image_bar['0']) && !empty($tooltip_image_bar['0'])) {
        $form['contact_us_form']['tooltip_url_bar'] = [
          '#type' => 'markup',
          '#markup' => get_engage_file_url_using_fid($tooltip_image_bar['0']),
          '#prefix' => '<div class ="hide">',
          '#suffix' => '</div>',
        ];
      }

      $tooltip_text_bar = $tooltip_config->get('dsu_engage_tooltip_text_bar_code_' . $lang_id);
      if (isset($tooltip_text_bar) && !empty($tooltip_text_bar)) {
        $form['contact_us_form']['tooltip_text_bar'] = [
          '#type' => 'markup',
          '#markup' => $tooltip_text_bar,
          '#prefix' => '<div class ="hide">',
          '#suffix' => '</div>',
        ];
      }

      $form['contact_us_form']['product']['barcode'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Bar code'),
        '#title_display' => 'invisible',
        '#attributes' => [
          'placeholder' => $this->t('Bar code'),
          'data-tool-tip' => 'has-bar-code',
        ],
        '#states' => [
          'visible' => $visible,
          'required' => $required,
        ],
        '#maxlength' => 64,
      ];
    }
    if ($config->get('show_field_best_before_date_question') || $config->get('show_field_best_before_date_complaint') || $config->get('show_field_best_before_date_praise')) {
      $visible = [];
      $required = [];
      if ($config->get('show_field_best_before_date_question')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'question'];
        if ($config->get('mandatory_field_best_before_date_question')) {
          $required[':input[name="request_type"]'][] = ['value' => 'question'];
        }

      }
      if ($config->get('show_field_best_before_date_complaint')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'complaint'];
        if ($config->get('mandatory_field_best_before_date_complaint')) {
          $required[':input[name="request_type"]'][] = ['value' => 'complaint'];
        }
      }
      if ($config->get('show_field_best_before_date_praise')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'praise'];
        if ($config->get('mandatory_field_best_before_date_praise')) {
          $required[':input[name="request_type"]'][] = ['value' => 'praise'];
        }
      }
      $form['contact_us_form']['product']['bestBeforeDate'] = [
        '#type' => 'date',
        '#title' => $this->t('Best before date'),
        '#default_value' => date('Y-m-d', time()),
        '#size' => 60,
        '#date_format' => 'Y-m-d',
        '#date_label_position' => 'none',
        '#title_display' => 'invisible',
        '#date_year_range' => '2000:2100',
        '#description' => $this->t('Best before date'),
        '#states' => [
          'visible' => $visible,
          'required' => $required,
        ],
      ];
    }

    $form['contact_us_form']['contact'] = [
      '#type' => 'details',
      '#title' => $this->t('Your Contact Information'),
      '#open' => TRUE,
    ];

    if ($config->get('show_field_first_name_question') || $config->get('show_field_first_name_complaint') || $config->get('show_field_first_name_praise')) {
      $visible = [];
      $required = [];
      if ($config->get('show_field_first_name_question')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'question'];
        if ($config->get('mandatory_field_first_name_question')) {
          $required[':input[name="request_type"]'][] = ['value' => 'question'];
        }

      }
      if ($config->get('show_field_first_name_complaint')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'complaint'];
        if ($config->get('mandatory_field_first_name_complaint')) {
          $required[':input[name="request_type"]'][] = ['value' => 'complaint'];
        }
      }
      if ($config->get('show_field_first_name_praise')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'praise'];
        if ($config->get('mandatory_field_first_name_praise')) {
          $required[':input[name="request_type"]'][] = ['value' => 'praise'];
        }
      }
      $form['contact_us_form']['contact']['firstName'] = [
        '#type' => 'textfield',
        '#title' => $this->t('First Name'),
        '#attributes' => [
          'placeholder' => $this->t('First Name'),
          'size' => '',
        ],
        '#title_display' => 'invisible',
        '#prefix' => '<div class="row"><div class="col-xs-12 col-12 col-md-6">',
        '#suffix' => '</div>',
        '#maxlength' => 64,
        '#states' => [
          'required' => $required,
          'visible' => $visible,
        ],
      ];
    }
    if ($config->get('show_field_last_name_question') || $config->get('show_field_last_name_complaint') || $config->get('show_field_last_name_praise')) {
      $visible = [];
      $required = [];
      if ($config->get('show_field_last_name_question')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'question'];
        if ($config->get('mandatory_field_last_name_question')) {
          $required[':input[name="request_type"]'][] = ['value' => 'question'];
        }

      }
      if ($config->get('show_field_last_name_complaint')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'complaint'];
        if ($config->get('mandatory_field_last_name_complaint')) {
          $required[':input[name="request_type"]'][] = ['value' => 'complaint'];
        }
      }
      if ($config->get('show_field_last_name_praise')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'praise'];
        if ($config->get('mandatory_field_last_name_praise')) {
          $required[':input[name="request_type"]'][] = ['value' => 'praise'];
        }
      }
      $form['contact_us_form']['contact']['lastName'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Last Name'),
        '#attributes' => [
          'placeholder' => $this->t('Last Name'),
          'size' => '',
        ],
        '#title_display' => 'invisible',
        '#prefix' => '<div class="col-xs-12 col-12 col-md-6">',
        '#suffix' => '</div></div>',
        '#maxlength' => 64,
        '#states' => [
          'required' => $required,
          'visible' => $visible,
        ],
      ];
    }
    $visible = [];
    $required = [];
    if ($config->get('show_field_preferred_channel_question') || $config->get('show_field_preferred_channel_complaint') || $config->get('show_field_preferred_channel_praise')) {
      if ($config->get('show_field_preferred_channel_question')) {
        $visible[] = [
          ':input[name="request_type"]' => ['value' => 'question'],
        ];
        if ($config->get('mandatory_field_preferred_channel_question')) {
          $required[':input[name="request_type"]'][] = ['value' => 'question'];
        }
      }
      if ($config->get('show_field_preferred_channel_complaint')) {
        $visible[] = [
          ':input[name="request_type"]' => ['value' => 'complaint'],
        ];
        if ($config->get('mandatory_field_preferred_channel_complaint')) {
          $required[':input[name="request_type"]'][] = ['value' => 'complaint'];
        }
      }
      if ($config->get('show_field_preferred_channel_praise')) {
        $visible[] = [
          ':input[name="request_type"]' => ['value' => 'praise'],
        ];
        if ($config->get('mandatory_field_preferred_channel_praise')) {
          $required[':input[name="request_type"]'][] = ['value' => 'praise'];
        }
      }

      $form['contact_us_form']['contact']['preferredChannel'] = [
        '#type' => 'select',
        '#title' => $this->t('How would you like us to get in touch with you?'),
        '#attributes' => [
          'placeholder' => $required ? $this->t('Preferred channel') . " *" : $this->t('Preferred channel'),
        ],
        '#default_value' => 0,
        '#options' => [
          'email' => $this->t("Email"),
          'phone' => $this->t("Phone"),
        ],
        '#states' => [
          'visible' => $visible,
          'required' => $required,
        ],
        '#ajax' => [
          'event' => 'change',
          'callback' => '::setRequiredField',
          'wrapper' => 'phone',
        ],
      ];
    }
    if (empty($visible)) {
      $visible[] = [
        ':input[name="request_type"]' => ['value' => ''],
      ];
    }
    $form['contact_us_form']['contact']['email'] = [
      '#type' => 'textfield',
      '#title_display' => 'invisible',
      '#title' => $this->t('Email address'),
      '#prefix' => '<div class="row"><div class="col-xs-12 col-12 col-md-6">',
      '#suffix' => '</div>',
      '#attributes' => [
        'placeholder' => $this->t('Email address') . " *",
        'type' => 'email',
      ],
      '#maxlength' => 64,
      '#states' => [
        'visible' => $visible,
      ],
      '#filters' => ['trim', 'lowercase'],
    ];

    // The conditions visible and required are set if preferredChannel is phone.
    $exclusionList = $config->get('dsu_engage_api_exclusion_list');
    $locationList = $config->get('dsu_engage_api_country_select');

    if (!empty($locationList && $exclusionList)) {
      $phone_array = array_flip(DsuEngageController::getPhonePrefixesValues());
      $prefix_result = array_intersect_key($phone_array, $locationList);
      $preexcresult = array_intersect_key($phone_array, $exclusionList);
      $prefixdiff = array_diff($prefix_result, $preexcresult);
      $phone_prefix = $prefixdiff;
    }
    else {
      $phone_prefix = array_flip(DsuEngageController::getPhonePrefixesValues());
    }
    $updated_phone_prefix = $this->get_translated_array_options($phone_prefix);
    $form['contact_us_form']['contact']['phone_prefix'] = [
      '#type' => 'select',
      '#title' => $this->t('Phone prefix'),
      '#title_display' => 'invisible',
      '#prefix' => '<div class="col-xs-12 col-12 col-md-6"><div class="row no-gutters"><div class="col-xs-12 col-12 col-md-12 col-lg-6">',
      '#suffix' => '</div>',
      '#options' => $updated_phone_prefix,
      '#default_value' => DsuEngageController::getCountryPrefix(DsuEngageController::getMarketCountries($config->get('dsu_engage_api_market'))),
      '#states' => [
        'visible' => $visible,
      ],
    ];
    // The conditions visible and required are set if preferredChannel is phone.
    if (!empty($user_data) && $user_data['preferredChannel'] == "phone") {
      $form['contact_us_form']['contact']['phone'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Phone'),
        '#title_display' => 'above',
        '#prefix' => '<div id="phone" class="col-xs-12 col-12 col-md-12 col-lg-6">',
        '#suffix' => '</div></div></div></div>',
        '#attributes' => [
          'placeholder' => $this->t('* Phone'),
        ],
        '#maxlength' => 64,
        '#states' => [
          'visible' => $visible,
          'required' => [
            ':input[name="preferredChannel"]' => ['value' => 'phone'],
          ],
        ],

      ];
    }
    else {
      $form['contact_us_form']['contact']['phone'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Phone'),
        '#title_display' => 'above',
        '#prefix' => '<div id="phone" class="col-xs-12 col-12 col-md-12 col-lg-6">',
        '#suffix' => '</div></div></div></div>',
        '#attributes' => [
          'placeholder' => $this->t('Phone'),
        ],
        '#maxlength' => 64,
        '#states' => [
          'visible' => $visible,
        ],

      ];
    }

    if ($config->get('show_field_street_question') || $config->get('show_field_street_complaint') || $config->get('show_field_street_praise')) {
      $visible = [];
      $required = [];
      if ($config->get('show_field_street_question')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'question'];
        if ($config->get('mandatory_field_street_question')) {
          $required[':input[name="request_type"]'][] = ['value' => 'question'];
        }
      }
      if ($config->get('show_field_street_complaint')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'complaint'];
        if ($config->get('mandatory_field_street_complaint')) {
          $required[':input[name="request_type"]'][] = ['value' => 'complaint'];
        }
      }
      if ($config->get('show_field_street_praise')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'praise'];
        if ($config->get('mandatory_field_street_praise')) {
          $required[':input[name="request_type"]'][] = ['value' => 'praise'];
        }
      }
      $form['contact_us_form']['contact']['street'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Street'),
        '#title_display' => 'invisible',
        '#attributes' => [
          'placeholder' => $this->t('Your Street'),
        ],
        '#states' => [
          'visible' => $visible,
          'required' => $required,
        ],
        '#rules' => [
          'length[20,255]',
        ],
      ];
    }
    if ($config->get('show_field_city_question') || $config->get('show_field_city_complaint') || $config->get('show_field_city_praise')) {
      $visible = [];
      $required = [];
      if ($config->get('show_field_city_question')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'question'];
        if ($config->get('mandatory_field_city_question')) {
          $required[':input[name="request_type"]'][] = ['value' => 'question'];
        }
      }
      if ($config->get('show_field_city_complaint')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'complaint'];
        if ($config->get('mandatory_field_city_complaint')) {
          $required[':input[name="request_type"]'][] = ['value' => 'complaint'];
        }
      }
      if ($config->get('show_field_city_praise')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'praise'];
        if ($config->get('mandatory_field_city_praise')) {
          $required[':input[name="request_type"]'][] = ['value' => 'praise'];
        }
      }
      $form['contact_us_form']['contact']['city'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Your City'),
        '#title_display' => 'invisible',
        '#attributes' => [
          'placeholder' => $this->t('City'),
        ],
        '#states' => [
          'visible' => $visible,
          'required' => $required,
        ],
        '#rules' => [
          'length[3,120]',
        ],
      ];
    }
    if ($config->get('show_field_zip_code_question') || $config->get('show_field_zip_code_complaint') || $config->get('show_field_zip_code_praise')) {
      $visible = [];
      $required = [];
      if ($config->get('show_field_zip_code_question')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'question'];
        if ($config->get('mandatory_field_zip_code_question')) {
          $required[':input[name="request_type"]'][] = ['value' => 'question'];
        }
      }
      if ($config->get('show_field_zip_code_complaint')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'complaint'];
        if ($config->get('mandatory_field_zip_code_complaint')) {
          $required[':input[name="request_type"]'][] = ['value' => 'complaint'];
        }
      }
      if ($config->get('show_field_zip_code_praise')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'praise'];
        if ($config->get('mandatory_field_zip_code_praise')) {
          $required[':input[name="request_type"]'][] = ['value' => 'praise'];
        }
      }
      $form['contact_us_form']['contact']['zipcode'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Zip code'),
        '#title_display' => 'invisible',
        '#attributes' => [
          'placeholder' => $this->t('Postal code'),
        ],
        '#states' => [
          'visible' => $visible,
          'required' => $required,
        ],
        '#maxlength' => 40,
      ];
    }
    if ($config->get('show_field_country_question') || $config->get('show_field_country_complaint') || $config->get('show_field_country_praise')) {
      $visible = [];
      $required = [];
      $selectedCountries = [];
      $selectedCountry = "";

      if ($config->get('show_field_country_question')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'question'];
        if ($config->get('mandatory_field_country_question')) {
          $required[':input[name="request_type"]'][] = ['value' => 'question'];
        }
      }
      if ($config->get('show_field_country_complaint')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'complaint'];
        if ($config->get('mandatory_field_country_complaint')) {
          $required[':input[name="request_type"]'][] = ['value' => 'complaint'];
        }
      }
      if ($config->get('show_field_country_praise')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'praise'];
        if ($config->get('mandatory_field_country_praise')) {
          $required[':input[name="request_type"]'][] = ['value' => 'praise'];
        }
      }

      // $countriesList = DsuEngageController::getCountries();
      if (!empty($locationList && $exclusionList)) {
        $country_repository = \Drupal::service('address.country_repository');
        $countries_array = $country_repository->getList();
        $result = array_intersect_key($countries_array, $locationList);
        $excresult = array_intersect_key($countries_array, $exclusionList);
        $countrydiff = array_diff($result, $excresult);
        $countriesList = $countrydiff;
      }
      else {
        $countriesList = DsuEngageController::getCountries();
      }
      $market = strtoupper($config->get('dsu_engage_api_market'));
      $country = strtoupper($config->get('dsu_engage_api_country'));
      if ($market !== 'GLOBAL') {
        $selectedCountries = DsuEngageController::getMarketCountries($market);
        $selectedCountry = strtoupper($country);
      }
      else {
        $selectedCountry = strtoupper($selectedCountries[0]);
      }

      $marketCountry = DsuEngageController::getCountryByAcronym($country);
      if (empty($marketCountry) || $market === 'GLOBAL' || $market === '') {
        $selectedCountry = "GLOBAL";
        $required = FALSE;
        $visible = FALSE;
      }

      if (!$countriesList) {
        $required = FALSE;
      }

      if (count($selectedCountries) > 1 || $market === 'GLOBAL' || $market === '') {
        $selectedCountry = "0";
      }

      $updated_countriesLiist = $this->get_translated_array_options($countriesList);
      if ($config->get('show_field_state_question') || $config->get('show_field_state_complaint') || $config->get('show_field_state_praise')) {
        $form['contact_us_form']['contact']['country'] = [
          '#type' => 'select',
          '#title' => $this->t('Country'),
          '#title_display' => 'before',
          '#prefix' => '<div class="row t"><div class="col-xs-12 col-12 col-md-6">',
          '#suffix' => '</div>',
          '#options' => $updated_countriesLiist,
          '#default_value' => $country,
          '#ajax' => [
            'event' => 'change',
            'callback' => '::getStatesAjax',
            'wrapper' => 'states',
          ],
          '#states' => [
            'visible' => $visible,
            'required' => $required,
          ],
        ];
      }
      else {
        $form['contact_us_form']['contact']['country'] = [
          '#type' => 'select',
          '#title' => $this->t('Country'),
          '#title_display' => 'before',
          '#prefix' => '<div class="col-xs-12 col-12 col-md-6 v">',
          '#suffix' => '</div>',
          '#options' => $updated_countriesLiist,
          '#default_value' => $country,
          '#states' => [
            'visible' => $visible,
            'required' => $required,
          ],
        ];
      }
    }
    if ($config->get('show_field_state_question') || $config->get('show_field_state_complaint') || $config->get('show_field_state_praise')) {
      $visible = [];
      $required = [];

      if ($config->get('show_field_state_question')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'question'];
        if ($config->get('mandatory_field_state_question')) {
          $required[':input[name="request_type"]'][] = ['value' => 'question'];
        }
      }
      if ($config->get('show_field_state_complaint')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'complaint'];
        if ($config->get('mandatory_field_state_complaint')) {
          $required[':input[name="request_type"]'][] = ['value' => 'complaint'];
        }
      }
      if ($config->get('show_field_state_praise')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'praise'];
        if ($config->get('mandatory_field_state_praise')) {
          $required[':input[name="request_type"]'][] = ['value' => 'praise'];
        }
      }

      if (!empty($user_data) && isset($user_data['country'])) {
        $selectedCountry = $user_data['country'];
      }
      $selectedStates = DsuEngageController::getStatesByCountry($selectedCountry);
      $form['contact_us_form']['contact']['state'] = [
        '#type' => 'select',
        '#title' => $this->t('State'),
        '#title_display' => 'before',
        '#prefix' => '<div id="states" class="col-xs-12 col-12 col-md-6">',
        '#suffix' => '</div></div>',
        '#options' => !empty($selectedStates) ? $this->get_translated_array_options($selectedStates) : [0 => $this->t('No states available')],
        '#states' => [
          'visible' => $visible,
          'required' => $required,
        ],
      ];
    }
    if ($config->get('show_field_attachments_question') || $config->get('show_field_attachments_complaint') || $config->get('show_field_attachments_praise')) {
      $visible = [];
      $required = [];
      if ($config->get('show_field_attachments_question')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'question'];
        if ($config->get('mandatory_field_attachments_question')) {
          $required[':input[name="request_type"]'][] = ['value' => 'question'];
        }
      }
      if ($config->get('show_field_attachments_complaint')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'complaint'];
        if ($config->get('mandatory_field_attachments_complaint')) {
          $required[':input[name="request_type"]'][] = ['value' => 'complaint'];
        }
      }
      if ($config->get('show_field_attachments_praise')) {
        $visible[':input[name="request_type"]'][] = ['value' => 'praise'];
        if ($config->get('mandatory_field_attachments_praise')) {
          $required[':input[name="request_type"]'][] = ['value' => 'praise'];
        }
      }
      $validators = [
        'file_validate_size' => [4.3 * 1024 * 1024],
        'file_validate_extensions' => [
          'xps pdf doc docx rtf txt xls xlsx csv bmp png jpeg jpg',
        ],
      ];

      // Add our upload validator to the widget, only if the
      // file_upload_secure_validator service is available.
      if (\Drupal::hasService('file_upload_secure_validator')) {
        $validators['file_upload_secure_validator_upload_validate'] = [];
      }
      $form['contact_us_form']['attachment'] = [
        '#type' => 'details',
        '#title' => $this->t('Attachments'),
        '#open' => TRUE,
        '#states' => [
          'visible' => $visible,
          'required' => $required,
        ],
      ];
      $form['contact_us_form']['attachment']['files'] = [
        '#type' => 'managed_file',
        '#preview' => TRUE,
        '#title' => $this->t('Upload files'),
        '#description' => $this->t("You can upload up to 9 files.<br/>The maximum size of each file is 4.3MB.<br/>Allowed file types: xps pdf doc docx rtf txt xls xlsx csv bmp png jpeg jpg"),
        '#upload_validators' => $validators,
        '#multiple' => TRUE,
        '#states' => [
          'visible' => $visible,
        ],
      ];
    }

    $form['contact_us_form']['captcha'] = [
      '#type' => 'captcha',
      '#captcha_type' => 'recaptcha/reCAPTCHA',
    ];

    if (!empty($config->get('dsu_engage_additional_footer_enable'))) {
      $form['contact_us_form']['campaign'] = [
        '#type' => 'markup',
        '#markup' => $this->t($config->get('dsu_engage_additional_footer_notes')) . ' <a href="tel:' . $config->get('dsu_engage_additional_footer_contact') . '">' . $config->get('dsu_engage_additional_footer_contact') . '</a>',
        '#prefix' => '<p>',
        '#suffix' => '</p>',
      ];
    }

    $consent_link_options = [
      'attributes' => [
        'target' => '_blank',
        'title' => 'Nestlé Privacy Notice',
      ],
    ];

    $privacy_policy_link_url = Url::fromUri('https://www.nescafe.com/privacy-policy');
    $privacy_policy_link_url->setOptions($consent_link_options);
    $privacy_policy_link = $this->link_generator->generate(t('Nestlé Privacy Notice'), $privacy_policy_link_url);

    $form['contact_us_form']['customer_consent'] = [
      '#type' => 'checkbox',
      '#name' => 'customer_consent',
      '#title' => $this->t('I agree to Nestlé processing my personal data as set out in the ' . $privacy_policy_link . '.'),
    ];

    $form['contact_us_form']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   *
   */
  public function getStatesAjax($form, FormStateInterface $form_state) {
    $states = [];
    $defaultValue = 0;
    if ($form_state->getValue('country') !== '0') {
      $states = DsuEngageController::getStatesByCountry($form_state->getValue('country'));
      $defaultValue = !empty($states) ? array_key_first($states) : 0;
    }
    $form['contact_us_form']['contact']['state']['#default_value'] = $defaultValue;
    $form['contact_us_form']['contact']['state']['#options'] = !empty($states) ? $this->get_translated_array_options($states) : [0 => $this->t('No states available')];

    return $form['contact_us_form']['contact']['state'];
  }

  /**
   *
   */
  public function setRequiredField(array &$form, FormStateInterface &$form_state) {
    if ($form_state->getValue('preferredChannel') == "phone") {
      $form['contact_us_form']['contact']['phone']['#attributes']['placeholder'] = $this->t('Phone *');
    }
    else {
      $form['contact_us_form']['contact']['phone']['#attributes']['placeholder'] = $this->t('Phone');
    }
    $form_state->setRebuild(TRUE);

    return $form['contact_us_form']['contact']['phone'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $user_date = $form_state->getValues();
    $config = $this->configFactory->get('dsu_engage.settings');
    $language = \Drupal::languageManager()->getCurrentLanguage()->getName();
    $market = strtoupper($config->get('dsu_engage_api_market'));
    $countryName = DsuEngageController::getCountryByAcronym($form_state->getValue('country'));
    $stateName = isset($user_date['state']) ? DsuEngageController::getStateByAcronym($form_state->getValue('country'), $form_state->getValue('state')) : '';

    $uploadedFiles = $form_state->getValue('files');
    // Managing attachment.
    $attachedFiles = [];
    if ($uploadedFiles) {
      foreach ($uploadedFiles as $uploadedFile) {
        $fileLoaded = $this->entityTypeManager->getStorage('file')
          ->load($uploadedFile);
        $fileContents = file_get_contents($fileLoaded->getFileUri());
        $fileEncoded = base64_encode($fileContents);
        $attachedFiles[] = [
          'attachmentName' => $fileLoaded->getFilename(),
          'attachmentBody' => $fileEncoded,
        ];
      }
    }
    if (!$form_state->hasAnyErrors()) {
      switch ($form_state->getValue('request_type')) {
        case "question":
          $requestType = "Question/Suggestion";
          break;

        case "complaint":
          $requestType = "Complaint";
          break;

        case "praise":
          $requestType = "Compliment";
          break;
      }

      $prefixesList = DsuEngageController::getPhonePrefixes();
      $prefix = ($form_state->getValue('phone_prefix') && isset($prefixesList[$form_state->getValue('phone_prefix')])) ? $prefixesList[$form_state->getValue('phone_prefix')] : "";
      $phone_new = $form_state->getValue('phone') ? $prefix . $form_state->getValue('phone') : "";
      $phonearr = explode("~", $phone_new);
      $phone = isset($phonearr[1]) ? $phonearr[1] : $phone_new;

      $data = [
        "brand" => $config->get('dsu_engage_api_brand'),
        "market" => $market,
        "country" => $countryName,
        "language" => $language,
        "consumerContactOrigin" => $config->get('dsu_engage_api_contact_origin'),
        "requestType" => $requestType,
        "firstName" => ucfirst($form_state->getValue('firstName')),
        "lastName" => ucfirst($form_state->getValue('lastName')),
        "email" => $form_state->getValue('email'),
        "street" => $form_state->getValue('street'),
        "zipCode" => $form_state->getValue('zipcode'),
        "city" => $form_state->getValue('city'),
        "state" => $stateName,
        "description" => $form_state->getValue('description'),
        "preferredChannel" => ucfirst($form_state->getValue('preferredChannel')),
        "phone" => $phone,
        "attachments" => $attachedFiles,
        "barCode" => $form_state->getValue('barcode'),
        "batchCode" => $form_state->getValue('batchcode'),
        "productDescription" => $form_state->getValue('product_description'),
        "JSONRequestId" => $form_state->getValue('form_build_id'),
      ];

      if ($form_state->getValue('request_type') == "complaint") {
        $data["bestBeforeDate"] = $form_state->getValue('bestBeforeDate') ? $form_state->getValue('bestBeforeDate') : NULL;
      }

      $token = DsuEngageController::dsuEngageGetToken();

      if (!$token['access_token']) {
        $this->messenger()
          ->addMessage($this->t('The access token is wrong. Your request was not sent to our contact system.'), 'error');
        $form_state->setRebuild();
      }

      $options = [
        'method' => 'POST',
        'json' => $data,
        'headers' => [
          'Content-Type' => 'application/json',
          'Authorization' => 'Bearer ' . $token['access_token'],
        ],
      ];

      $response = DsuEngageController::dsuEngageHttpRequest(
        $config->get('dsu_engage_api_endpoint_url'),
        $options
      );

      $responseOk = $response->getStatusCode() === 200;
      $responseData = Json::decode(Json::decode($response->getBody()));

      if ($responseData['status'] === 'failure') {
        $this->messenger()
          ->addMessage($responseData['ErrorMessage'], 'error');
        $form_state->setRebuild();
      }

      if ($responseOk && $responseData['status'] !== 'failure') {
        // Confirmation message.
        $request_type = $form_state->getValue('request_type');
        $event_label = (($request_type == 'question') ? 'question' : (($request_type == 'complaint') ? 'complaint' : (($request_type == 'praise') ? 'praise' : '')));
        $formatted_message = new TranslatableMarkup(
          '<div id = "engage-success" data-eventlabel = "@event_label">Your ticket is created. Your contact number is: @ticket
      </div>',
          [
            '@event_label' => $event_label,
            '@ticket' => $responseData['caseNumber'],
          ]
        );

        if ( \Drupal::hasService('ln_datalayer.events') ) {
          \Drupal::service('ln_datalayer.events')->addEvent("form_submit_{$form['#id']}", [
            'event' => 'contact_interaction',
            'event_name' => 'contactus_submit',
            'form_name' => $form['#id'],
            'form_id' => $form['#id'],
            'topic' => $form_state->getValue('request_type'),
            'module_name' => $form['#attached']['drupalSettings']['dsu_engage']['data']['module_name'] ,
            'module_version' => $form['#attached']['drupalSettings']['dsu_engage']['data']['module_version'] ,
          ]);
        }
        $this->messenger()
          ->addMessage($this->t(' @message', ['@message' => $formatted_message]));
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    // Check for ajax callback. Validate fields only submit button.
    if ($triggering_element['#name'] == 'op') {
      $config = $this->configFactory->get('dsu_engage.settings');
      $selectedTab = $form_state->getValue('request_type');
      $description = $form_state->getValue('description');
      $emailaddress = trim($form_state->getValue('email'));
      $phone = trim($form_state->getValue('phone'));
      $lastName = trim($form_state->getValue('lastName'));
      $firstName = trim($form_state->getValue('firstName'));
      $selectedCountry = $form_state->getValue('country');
      $uploadedFiles = $form_state->getValue('files');
      $bestBeforeDate = $form_state->getValue('bestBeforeDate');
      $productDescription = $form_state->getValue('product_description');
      $barCode = $form_state->getValue('barcode');
      $batchCode = $form_state->getValue('batchcode');
      $street = $form_state->getValue('street');
      $city = $form_state->getValue('city');
      $zipcode = $form_state->getValue('zipcode');
      $state = $form_state->getValue('state');
      $preferredChannel = $form_state->getValue('preferredChannel');
      $customer_term_conditions = $form_state->getValue('customer_consent');

      // Validate Product information.
      if ($selectedTab === 'question' || $selectedTab === 'complaint' || $selectedTab === 'praise') {
        $required_product_description = $config->get('mandatory_field_product_description_' . $selectedTab);
        $required_batch_code = $config->get('mandatory_field_batch_code_' . $selectedTab);
        $required_bar_code = $config->get('mandatory_field_bar_code_' . $selectedTab);
        $required_data = $config->get('mandatory_field_best_before_date_' . $selectedTab);
        if ($description === "") {
          $form_state->setErrorByName('description', $this->t('Please enter your message.'));
        }
        if ($required_product_description && $productDescription === "") {
          $form_state->setErrorByName('product_description', $this->t('Please enter a product description.'));
        }
        if ($required_batch_code && $batchCode === "") {
          $form_state->setErrorByName('batchcode', $this->t('Please enter a Batch code.'));
        }
        if ($required_bar_code && $barCode === "") {
          $form_state->setErrorByName('barcode', $this->t('Please enter a Bar code.'));
        }
        if ($required_data && $bestBeforeDate === "") {
          $form_state->setErrorByName('bestBeforeDate', $this->t('Please enter data.'));
        }
        if ($bestBeforeDate != NULL) {
          if (!$this->validateDate($bestBeforeDate, 'Y-m-d')) {
            $form_state->setErrorByName('bestBeforeDate', $this->t('Please enter a valid date.'));
          }
        }
      }
      // Validate contact information.
      if ($selectedTab === 'question' || $selectedTab === 'complaint' || $selectedTab === 'praise') {
        $required_first_name = $config->get('mandatory_field_first_name_' . $selectedTab);
        $required_last_name = $config->get('mandatory_field_last_name_' . $selectedTab);
        if ($required_first_name && $firstName === "") {
          $form_state->setErrorByName('firstName', $this->t('The field "First Name" is missing.'));
        }
        // Firstname accept only text, space and langugae specific special chars (eg, É,á,Á etc.).
        if ($firstName && !preg_match('/^(\p{L}(\.){0,1}(\s){0,1})+$/iu', $firstName)) {
          $form_state->setErrorByName('firstName', $this->t('The field "First Name" only accepts text.'));
        }
        // Lastname accept only text, space and langugae specific special chars (eg, É,á,Á etc.).
        if ($required_last_name && $lastName === "") {
          $form_state->setErrorByName('lastName', $this->t('The field "Last Name" is missing.'));
        }
        if ($lastName && !preg_match('/^(\p{L}(\.){0,1}(\s){0,1})+$/iu', $lastName)) {
          $form_state->setErrorByName('lastName', $this->t('The field "Last Name" only accepts text.'));
        }
      }
      if ($preferredChannel == 'email' && $emailaddress == '') {
        $form_state->setErrorByName('email', $this->t('Please enter an email address.'));
      }
      else {
        if (!empty($emailaddress) && !$this->emailValidator->isValid($emailaddress)) {
          $form_state->setErrorByName('email', $this->t('%emailaddress is an invalid email address.', ['%emailaddress' => $emailaddress]));
        }
      }
      if ($preferredChannel == 'phone' && $phone == '') {
        $form_state->setErrorByName('phone', $this->t('Please enter a phone number.'));
      }
      if ($phone && !ctype_digit($phone)) {
        $form_state->setErrorByName('phone', $this->t('The field "Phone" only accepts numeric characters.'));
      }

      // Validate contact information.
      if ($selectedTab === 'question' || $selectedTab === 'complaint' || $selectedTab === 'praise') {
        $required_street = $config->get('mandatory_field_street_' . $selectedTab);
        $required_city = $config->get('mandatory_field_city_' . $selectedTab);
        $required_zipcode = $config->get('mandatory_field_zip_code_' . $selectedTab);
        $required_state = $config->get('mandatory_field_state_' . $selectedTab);
        $required_attachment = $config->get('mandatory_field_attachments_' . $selectedTab);
        $required_country = $config->get('mandatory_field_country_' . $selectedTab);
        if ($required_street && $street === "") {
          $form_state->setErrorByName('street', $this->t('Please enter a street.'));
        }
        if ($required_city && $city === "") {
          $form_state->setErrorByName('city', $this->t('Please enter a city.'));
        }
        if ($required_zipcode && $zipcode === "") {
          $form_state->setErrorByName('zipcode', $this->t('Please enter a zipcode.'));
        }
        if ($required_state && $state === "0") {
          $form_state->setErrorByName('state', $this->t('Please select a state.'));
        }
        if ($required_country && $selectedCountry === "0") {
          $form_state->setErrorByName('country', $this->t('Please select a country.'));
        }
        if ($required_attachment && (!empty($uploadedFiles) && count($uploadedFiles) == "0")) {
          $form_state->setErrorByName('files', $this->t('Upload files field is required.'));
        }
      }
      if (!empty($uploadedFiles) && count($uploadedFiles) > 9) {
        $form_state->setErrorByName('files', $this->t('The number of files uploaded exceed the maximum of 9 files allowed.'));
      }

      if (!$customer_term_conditions) {
        $form_state->setErrorByName('customer_consent', $this->t('Please accept Nestle terms & condtions.'));
      }

      if ($form_state->getErrors()) {
        $form['engage_error'] = [
          '#type' => 'markup',
          '#prefix' => '<div class ="engage-error">',
          '#suffix' => '</div>',
        ];
      }

      //Set event error
      foreach ($form_state->getErrors() as $key => $err){
        // Only use the service if the module is enabled.
        if ( \Drupal::hasService('ln_datalayer.events') ) {
          \Drupal::service('ln_datalayer.events')->addEvent("form_validate_{$form['#id']}", [
            'event' => 'contact_error',
            'event_name' => 'contactus_submit_error',
            'form_name' => $form['#id'],
            'form_id' => $form['#id'],
            'topic' => $form_state->getValue('request_type'),
            'error_code' => $key,
            'error_name' => $err,
            'module_name' => $form['#attached']['drupalSettings']['dsu_engage']['data']['module_name'] ,
            'module_version' => $form['#attached']['drupalSettings']['dsu_engage']['data']['module_version'] ,
          ]);
        }
      }
    }
  }

  /**
   *
   */
  public function validateDate($myDateString) {
    return (bool) strtotime($myDateString);
  }

  /**
   * Add translation on the option values.
   *
   * @param $array_list
   *
   * @return array
   */
  public function get_translated_array_options($array_list) {
    $updatedList = [];
    if (!empty($array_list)) {
      foreach ($array_list as $code => $name) {
        $updatedList[$code] = $this->t($name);
      }
    }
    return $updatedList;
  }

}

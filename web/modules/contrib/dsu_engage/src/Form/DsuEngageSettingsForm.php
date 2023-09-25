<?php

namespace Drupal\dsu_engage\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class DsuEngageSettingsForm.
 *
 * @package Drupal\dsu_engage\Form
 */
class DsuEngageSettingsForm extends ConfigFormBase {

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
    return 'dsu_engage_settings';
  }

  /**
   * Constructs a SettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory onject.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    parent::__construct($config_factory);
    $this->configFactory = $config_factory;
  }

  /**
   *
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $currentUser = $this->currentUser();
    $default_tab = !empty($form_state->getValue('default_tab')) ? $form_state->getValue('default_tab') : '';

    // Build form tabs.
    $form['tabs'] = [
      '#type' => 'vertical_tabs',
      '#default_tab' => $default_tab ? $default_tab : 'edit-general',
      '#attributes' => ['class' => ['google-tag']],
    ];

    // API Settings Tab.
    $form['engage_api'] = [
      '#type' => 'details',
      '#title' => $this->t('Engage API'),
      '#description' => $this->t('On this tab, enter the settings to conect with engage.'),
      '#access' => $currentUser->hasPermission("administer advanced dsu engage"),
      '#group' => 'tabs',
    ];

    $form['engage_api'] += $this->buildAPISettingsFrom();

    // Contact Us Block Form Settings Tab.
    $form['contact_us_block_form'] = [
      '#type' => 'details',
      '#title' => $this->t('Contact Us Block Form'),
      '#description' => $this->t('Send Contact Us webform data to Engage based on the following configurations.'),
      '#access' => $currentUser->hasPermission("administer dsu engage"),
      '#group' => 'tabs',
    ];

    // Build form elements.
    $form['form_elements'] = [
      '#type' => 'details',
      '#title' => $this->t('Contact Us Page'),
      '#description' => $this->t('On this tab, select the fields which will appear on your contact form.'),
      '#access' => $currentUser->hasPermission("administer dsu engage"),
      '#group' => 'tabs',
    ];

    $form['form_elements'] += $this->buildFieldsSettingsFrom();

    // Build form elements.
    $form['form_elements_footer_notes'] = [
      '#type' => 'details',
      '#title' => $this->t('Additional Footer Notes contact'),
      '#description' => $this->t('On this tab, select the fields which will appear on your contact form.'),
      '#access' => $currentUser->hasPermission("administer dsu engage"),
      '#group' => 'tabs',
    ];

    $form['form_elements_footer_notes'] += $this->buildFootersSettingsFrom();

    return parent::buildForm($form, $form_state);
  }

  /**
   *
   */
  private function buildAPISettingsFrom() {
    $currentUser = $this->currentUser();
    $config = $this->configFactory->get('dsu_engage.settings');

    $settingsForm['general'] = [
      '#type' => 'details',
      '#title' => t('Common Configuration'),
      '#open' => TRUE,
    ];
    $settingsForm['general']['dsu_engage_api_token_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Engage API Endpoint Token URL'),
      '#description' => $this->t('To connect with Engage, REST API requests need to be send to a Endpoint Token URL'),
      '#default_value' => $config->get('dsu_engage_api_token_url'),
      '#access' => $currentUser->hasPermission("administer full dsu engage"),
      '#size' => 200,
    ];
    $settingsForm['general']['dsu_engage_api_endpoint_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Engage API Endpoint URL'),
      '#description' => $this->t('To connect with Engage, REST API requests need to be send to a Endpoint URL'),
      '#default_value' => $config->get('dsu_engage_api_endpoint_url'),
      '#access' => $currentUser->hasPermission("administer full dsu engage"),
      '#size' => 200,
    ];
    $settingsForm['general']['dsu_engage_api_client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Engage API Client ID'),
      '#description' => $this->t('Please enter Engage API Client ID'),
      '#default_value' => $config->get('dsu_engage_api_client_id'),
      '#size' => 200,
    ];
    $settingsForm['general']['dsu_engage_api_client_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Engage API Client Secret'),
      '#description' => $this->t('Please enter Engage API Client Secret Key'),
      '#default_value' => $config->get('dsu_engage_api_client_secret'),
    ];

    $settingsForm['user_details'] = [
      '#type' => 'details',
      '#title' => t('User Details'),
      '#open' => TRUE,
    ];
    $settingsForm['user_details']['dsu_engage_api_user_username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Engage API User Username'),
      '#description' => $this->t('Please enter Engage API User Username'),
      '#default_value' => $config->get('dsu_engage_api_user_username'),
    ];

    $settingsForm['user_details']['password_details'] = [
      '#type' => 'details',
      '#title' => t('Password Details'),
      '#open' => TRUE,
    ];
    $settingsForm['user_details']['password_details']['dsu_engage_api_user_password'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Engage API User Password'),
      '#description' => $this->t('Please enter Engage API User Password'),
      '#default_value' => $config->get('dsu_engage_api_user_password'),
    ];
    $expirationDate = new \DateTime(date('m/d/Y', $config->get('dsu_engage_password_expiration')));
    $currentDate = new \DateTime(date('m/d/Y', time()));
    $interval = $expirationDate->diff($currentDate);

    $settingsForm['user_details']['password_details']['dsu_engage_password_expiration'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Token Expiration'),
      '#description' => $this->t('Days left to expire the password @password_expiration', ['@password_expiration' => $interval->days]),
      '#default_value' => date('m/d/Y', $config->get('dsu_engage_password_expiration')),
      '#attributes' => ['disabled' => TRUE],
    ];

    $settingsForm['user_details']['certificate_details'] = [
      '#type' => 'details',
      '#title' => t('Certificate Details'),
      '#open' => TRUE,
    ];
    $settingsForm['user_details']['certificate_details']['dsu_engage_api_client_certificate'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Engage API client certificate path'),
      '#description' => $this->t("Enter the path of the client certificate location in this server. If you don't know, contact WebCMS team."),
      '#default_value' => $config->get('dsu_engage_api_client_certificate'),
      '#attributes' => ['placeholder' => $this->t('/var/www/cert.key')],
    ];
    $settingsForm['user_details']['certificate_details']['dsu_engage_api_audience_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Engage API Audience URL'),
      '#description' => $this->t("To connect with Engage, REST API requests need to be send to a Audience URL"),
      '#default_value' => $config->get('dsu_engage_api_audience_url'),
    ];
    $settingsForm['market_details'] = [
      '#type' => 'details',
      '#title' => t('Market Details'),
      '#open' => TRUE,
    ];
    $settingsForm['market_details']['dsu_engage_api_brand'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Engage API Brand'),
      '#description' => $this->t('Please enter Engage API Brand (Case sentitive)'),
      '#default_value' => $config->get('dsu_engage_api_brand'),
    ];
    $settingsForm['market_details']['dsu_engage_api_market'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Engage API Market'),
      '#description' => $this->t('Please enter Engage API Market (2 Digits)'),
      '#default_value' => $config->get('dsu_engage_api_market'),
    ];
    $settingsForm['market_details']['dsu_engage_api_country'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Engage API Country'),
      '#description' => $this->t('Please enter Engage API Country (2 Digits)'),
      '#default_value' => $config->get('dsu_engage_api_country'),
    ];
    $settingsForm['market_details']['dsu_engage_api_contact_origin'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Engage API Contact Origin'),
      '#description' => $this->t('Website url without http(s)'),
      '#default_value' => $config->get('dsu_engage_api_contact_origin'),
    ];

    return $settingsForm;
  }

  /**
   * @return mixed
   */
  private function buildFieldsSettingsFrom() {

    $config = $this->configFactory->get('dsu_engage.settings');

    $settingsForm['enable_engage_form'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Engage form Page'),
      '#description' => $this->t('Enable the Contact Us page with settings below.'),
      '#default_value' => $config->get('enable_engage_form'),
    ];

    $settingsForm['engage_form_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter Contact Us page URL'),
      '#description' => $this->t('Note : URL must be internal and unique (Ex : contact_us)'),
      '#default_value' => $config->get('engage_form_path'),
      '#states' => [
        'visible' => [
          ':input[name="enable_engage_form"]' => ['checked' => TRUE],
        ],
        'required' => [
          ':input[name="enable_engage_form"]' => ['checked' => TRUE],
        ],
      ],
    ];

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
      // 4 => ['name' => 'field_email', 'label' => 'E-Mail'],
      // 5 => ['name' => 'field_phone', 'label' => 'Phone'],
      6 => ['name' => 'field_batch_code', 'label' => 'Batch Code'],
      7 => ['name' => 'field_bar_code', 'label' => 'Bar Code'],
      8 => [
        'name' => 'field_best_before_date',
        'label' => 'Best before date',
      ],
      9 => ['name' => 'field_street', 'label' => 'Street'],
      10 => ['name' => 'field_city', 'label' => 'City'],
      11 => ['name' => 'field_zip_code', 'label' => 'Zip Code'],
      12 => ['name' => 'field_state', 'label' => 'State'],
      13 => ['name' => 'field_country', 'label' => 'Country'],
      14 => ['name' => 'field_attachments', 'label' => 'Attachments'],
    ];

    $tableMarkup = '<table id="responsive - preview - devices - list" class="sticky - enabled dsu_enage_fields_settings">';
    $tableHead = '<thead><tr><th></th><th colspan="2">' . $this->t('Question') . '</th><th colspan="2">' . $this->t('Complaint') . '</th><th colspan="2">' . $this->t('Praise') . '</th></tr></thead>';
    $tableSubHead = '<tr><td></td><td>' . $this->t('Show') . '</td><td>' . $this->t('Required') . '</td><td>' . $this->t('Show') . '</td><td>' . $this->t('Required') . '</td><td>' . $this->t('Show') . '</td><td>' . $this->t('Required') . '</td>';

    $settingsForm['table_markup'] = [
      '#type' => 'markup',
      '#markup' => $tableMarkup . $tableHead . $tableSubHead,
      '#attached' => [
        'library' => [
          'dsu_engage/dsu_engage_form_admin',
        ],
      ],
    ];

    $settingsForm['show_field_request_type_options'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Request Type'),
      '#title_display' => 'above',
      '#description' => $this->t('Select the options'),
      '#options' => [
        'question' => $this->t("I have a question/suggestion"),
        'complaint' => $this->t("I have a complaint"),
        'praise' => $this->t("I have a praise"),
      ],
      'question' => [
        '#attributes' => $config->get('show_field_request_type_options.question') ? ['checked' => 'checked'] : [],
      ],
      'complaint' => [
        '#attributes' => $config->get('show_field_request_type_options.complaint') ? ['checked' => 'checked'] : [],
      ],
      'praise' => [
        '#attributes' => $config->get('show_field_request_type_options.praise') ? ['checked' => 'checked'] : [],

      ],
    ];
    // Basic Fields.
    foreach ($fields as $index => $fieldValues) {
      $fieldName = $fieldValues['name'];
      $fieldLabel = $fieldValues['label'];
      // Question.
      $settingsForm['show_' . $fieldName . '_question'] = [
        '#type' => 'checkbox',
        '#attributes' => $config->get('show_' . $fieldName . '_question') ? ['checked' => 'checked'] : [],
        '#prefix' => '<tr><td>' . $this->t($fieldLabel) . '</td><td>',
        '#suffix' => '</td>',
      ];

      $settingsForm['mandatory_' . $fieldName . '_question'] = [
        '#type' => 'checkbox',
        '#attributes' => $config->get('mandatory_' . $fieldName . '_question') ? ['checked' => 'checked'] : [],
        '#prefix' => '<td>',
        '#suffix' => '</td>',
      ];

      // Complaint.
      $settingsForm['show_' . $fieldName . '_complaint'] = [
        '#type' => 'checkbox',
        '#attributes' => $config->get('show_' . $fieldName . '_complaint') ? ['checked' => 'checked'] : [],
        '#prefix' => '<td>',
        '#suffix' => '</td>',
      ];

      $settingsForm['mandatory_' . $fieldName . '_complaint'] = [
        '#type' => 'checkbox',
        '#attributes' => $config->get('mandatory_' . $fieldName . '_complaint') ? ['checked' => 'checked'] : [],
        '#prefix' => '<td>',
        '#suffix' => '</td>',
      ];

      // Praise.
      $settingsForm['show_' . $fieldName . '_praise'] = [
        '#type' => 'checkbox',
        '#attributes' => $config->get('show_' . $fieldName . '_praise') ? ['checked' => 'checked'] : [],
        '#prefix' => '<td>',
        '#suffix' => '</td>',
      ];

      $settingsForm['mandatory_' . $fieldName . '_praise'] = [
        '#type' => 'checkbox',
        '#attributes' => $config->get('mandatory_' . $fieldName . '_praise') ? ['checked' => 'checked'] : [],
        '#prefix' => '<td>',
        '#suffix' => '</td></tr>',
      ];
    }

    $settingsForm['table_markup_end'] = [
      '#type' => 'markup',
      '#markup' => '</table>',
    ];

    return $settingsForm;
  }

  /**
   * @return mixed
   */
  private function buildFootersSettingsFrom() {
    $currentUser = $this->currentUser();
    $config = $this->configFactory->get('dsu_engage.settings');

    $settingsForm['dsu_engage_additional_footer_enable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Engage Additional Notes'),
      '#description' => $this->t('Additional contact notes.'),
      '#default_value' => $config->get('dsu_engage_additional_footer_enable'),
      '#access' => $currentUser->hasPermission("administer full dsu engage"),
    ];

    $settingsForm['dsu_engage_additional_footer_notes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Engage Additional Footer Notes'),
      '#description' => $this->t('Additional contact notes.'),
      '#default_value' => $config->get('dsu_engage_additional_footer_notes'),
      '#access' => $currentUser->hasPermission("administer full dsu engage"),
      '#size' => 200,
    ];
    $settingsForm['dsu_engage_additional_footer_contact'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Additional Contact No'),
      '#description' => $this->t('Additional Contact No.'),
      '#default_value' => $config->get('dsu_engage_additional_footer_contact'),
      '#access' => $currentUser->hasPermission("administer full dsu engage"),
      '#size' => 200,
    ];

    return $settingsForm;

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->configFactory->getEditable('dsu_engage.settings');

    $currentPassword = $config->get('dsu_engage_api_user_password');
    $newPassword = $form_state->getValue('dsu_engage_api_user_password');

    $passwordExpiration = $config->get('dsu_engage_password_expiration') ?: strtotime('+90 days', time());
    if ($currentPassword !== $newPassword) {
      $passwordExpiration = strtotime('+90 days', $passwordExpiration);
    }

    foreach ($form_state->getValues() as $key => $value) {
      $config->set($key, $value)->save();
    }

    drupal_flush_all_caches();
    $this->messenger()
      ->addMessage($this->t('dsu_engage_password_expiration: @password_expiration .', [
        '@password_expiration' => $passwordExpiration,
      ]));
    $config->set('dsu_engage_password_expiration', $passwordExpiration)
      ->save();
  }

}

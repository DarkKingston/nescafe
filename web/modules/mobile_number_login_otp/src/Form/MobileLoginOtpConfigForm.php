<?php

namespace Drupal\mobile_number_login_otp\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * MobileNumberOtp Admin Form.
 */
class MobileLoginOtpConfigForm extends ConfigFormBase {

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
    $this->settings = 'mobilenumberlogin.otp.settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mobile_login_otp_settings_form';
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
    $form['api_key'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Api Key'),
      '#default_value' => $config->get('api_key'),
      '#description' => $this->t('Fetch the API Key from the kaleyra') . ' ' . Link::fromTextAndUrl($this->t('read here.'), Url::fromUri('https://developers.kaleyra.io/docs/sms-api'))->toString(),
    ];
    $form['otp_expiry'] = [
      '#type' => 'number',
      '#title' => $this->t('OTP Expiry'),
      '#default_value' => $config->get('otp_expiry'),
      '#description' => $this->t('Add it in seconds'),
    ];
    $form['sender_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sender'),
      '#default_value' => $config->get('sender_id'),
    ];
    $form['sid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SID'),
      '#default_value' => $config->get('sid'),
    ];
    $form['welcome_otp'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Welcome OTP ID'),
      '#default_value' => $config->get('welcome_otp'),
    ];
    $form['login_otp'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Login OTP ID'),
      '#default_value' => $config->get('login_otp'),
    ];
    $form['appointment_otp'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Appointment OTP ID'),
      '#default_value' => $config->get('appointment_otp'),
    ];
    $form['otp_max_count'] = [
      '#type' => 'number',
      '#title' => $this->t('OTP Max Count'),
      '#default_value' => $config->get('otp_max_count'),
    ];

    $form['templates'] = [
      '#type' => 'table',
      '#tree' => TRUE,
      '#prefix' => '<div id="templates-wrapper">',
      '#suffix' => '</div>',
      '#header' => [
        $this->t('Item'),
        $this->t('Weight'),
        $this->t('Template content'),
        $this->t('Actions'),
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'mytable-order-weight',
        ],
      ],
    ];

    $weights = $config->get('templates');
    if (empty($weights)) {
      $weights[] = [
        'weight' => 0,
        'template' => [],
      ];
    }
    uasort($weights, 'Drupal\Component\Utility\SortArray::sortByWeightElement');
    foreach ($weights as $delta => $weight) {
      $form['templates'][$delta]['#attributes']['class'][] = 'draggable';
      $form['templates'][$delta]['#weight'] = !empty($weight['weight']) ? $weight['weight'] : 0;
      $form['templates'][$delta]['item'] = [
        '#plain_text' => !empty($weight['weight']) ? $weight['weight'] : 0,
      ];
      $form['templates'][$delta]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => 'template']),
        '#title_display' => 'invisible',
        '#attributes' => ['class' => ['mytable-order-weight']],
        '#default_value' => !empty($weight['weight']) ? $weight['weight'] : 0,
      ];
      $form['templates'][$delta]['template'] = [
        '#type' => 'details',
        '#tree' => TRUE,
        '#title' => $this->t('Template @counter', ['@counter' => ($delta + 1)]),
        '#collapsible' => TRUE,
      ];
      $form['templates'][$delta]['template']['type'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Type'),
        '#default_value' => $weight['template']['type'],
      ];
      $form['templates'][$delta]['template']['template_id'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Template ID'),
        '#default_value' => $weight['template']['template_id'],
      ];
      $form['templates'][$delta]['template']['body'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Body'),
        '#default_value' => $weight['template']['body'],
      ];
      if (count($weights) > 1) {
        $form['templates'][$delta]['delete'] = [
          '#type' => 'submit',
          '#title' => $this->t('Remove'),
          '#name' => 'delete_' . $delta,
          '#value' => 'Remove',
          '#submit' => ['::ajaxSubmit'],
          '#ajax' => [
            'callback' => '::addMoreSet',
            'wrapper' => 'templates-wrapper',
          ],
        ];
      }
      else {
        $form['templates'][$delta]['delete'] = [];
      }
    }
    $form['add'] = [
      '#type' => 'submit',
      '#title' => $this->t('Add template'),
      '#value' => $this->t('Add more'),
      '#submit' => ['::ajaxSubmit'],
      '#ajax' => [
        'callback' => '::addMoreSet',
        'wrapper' => 'templates-wrapper',
      ],
    ];

    return $form;
  }

  /**
   * Function addMoreSet.
   */
  public function addMoreSet(array &$form, FormStateInterface $form_state) {
    return $form['templates'];
  }

  /**
   * Function ajaxSubmit.
   */
  public function ajaxSubmit(array &$form, FormStateInterface $form_state) {
    $weights = $form_state->getValue('templates');
    $parents = $form_state->getTriggeringElement()['#parents'];
    if (isset($parents[0]) && $parents[0] == 'add') {
      $weights[] = [
        'weight' => count($weights),
        'template' => [],
      ];
    }
    if (isset($parents[2]) && $parents[2] == 'delete') {
      unset($weights[$parents[1]]);
    }

    $form_state->setValue('templates', $weights);
    $form_state->setRebuild(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $conf = $this->config($this->settings)
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('sender_id', $form_state->getValue('sender_id'))
      ->set('sid', $form_state->getValue('sid'))
      ->set('welcome_otp', $form_state->getValue('welcome_otp'))
      ->set('login_otp', $form_state->getValue('login_otp'))
      ->set('appointment_otp', $form_state->getValue('appointment_otp'))
      ->set('otp_expiry', $form_state->getValue('otp_expiry'))
      ->set('otp_max_count', $form_state->getValue('otp_max_count'))
      ->set('templates', $form_state->getValue('templates'));
    if (!empty($form_state->getValue('templates'))) {
      $templates = [];
      foreach ($form_state->getValue('templates') as $tempValue) {
        if (!empty($tempValue['template']['template_id'])) {
          $templates[$tempValue['template']['template_id']] = [
            'type' => $tempValue['template']['type'],
            'body' => $tempValue['template']['body'],
          ];
        }
      }
      $conf->set('templatesWrapped', $templates);
    }
    $conf->save();
    return parent::submitForm($form, $form_state);
  }

}

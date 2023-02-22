<?php

namespace Drupal\ln_ciam\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Defines the General settings form.
 */
class GeneralSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ln_ciam_general_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ln_ciam.settings'];
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ln_ciam.settings');
    $gigya_config = $this->config('gigya.settings');
    $is_gigya_config = !empty($gigya_config->get('gigya.gigya_api_key'));
    if(!$is_gigya_config){
      $form['alert'] = [
        '#theme' => 'status_messages',
        '#message_list' => [
          'warning' => [
            $this->t('Please, fill the <a href=":gigya_url">gigya data</a> first.', [':gigya_url' => Url::fromRoute('gigya.admin.form')->toString()])
          ]
        ],
      ];
    }

    $form['login_register_links'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Login and Register'),
      '#disabled' => !$is_gigya_config
    ];
    $form['login_register_links']['enable_login'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Alter login links by gigya screensets'),
      '#default_value' => $config->get('enable_login'),
    ];

    $subfield_states = [
      'visible' => [
        ':input[name="enable_login"]' => [
          'checked' => TRUE
        ],
      ],
      'disabled' => [
        ':input[name="enable_login"]' => [
          'checked' => FALSE
        ],
        'or',
        ':input[name="enable_login"]' => [
          'disabled' => TRUE
        ],
      ]
    ];
    $form['login_register_links']['login_screenset'] = [
      '#type' => 'textfield',
      '#title'=> $this->t('Screenset'),
      '#default_value' => $config->get('login_screenset'),
      '#required' => TRUE,
      '#states' => $subfield_states
    ];

    $form['login_register_links']['login_screen'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Screen'),
      '#default_value' => $config->get('login_screen'),
      '#required' => TRUE,
      '#states' => $subfield_states
    ];

    $form['login_register_links']['enable_register'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Alter register links by gigya screensets'),
      '#default_value' => $config->get('enable_register'),
    ];

    $subfield_states = [
      'visible' => [
        ':input[name="enable_register"]' => [
          'checked' => TRUE
        ],
      ],
      'disabled' => [
        ':input[name="enable_register"]' => [
          'checked' => FALSE
        ],
        'or',
        ':input[name="enable_register"]' => [
          'disabled' => TRUE
        ],
      ]
    ];
    $form['login_register_links']['register_screenset'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Screenset'),
      '#default_value' => $config->get('register_screenset'),
      '#required' => TRUE,
      '#states' => $subfield_states
    ];

    $form['login_register_links']['register_screen'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Screen'),
      '#default_value' => $config->get('register_screen'),
      '#required' => TRUE,
      '#states' => $subfield_states
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::submitForm().
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('ln_ciam.settings')
      ->set('enable_login', $form_state->getValue('enable_login'))
      ->set('login_screenset', $form_state->getValue('login_screenset'))
      ->set('login_screen', $form_state->getValue('login_screen'))
      ->set('enable_register', $form_state->getValue('enable_register'))
      ->set('register_screenset', $form_state->getValue('register_screenset'))
      ->set('register_screen', $form_state->getValue('register_screen'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}

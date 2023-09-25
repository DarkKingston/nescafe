<?php

namespace Drupal\google_optimize_js\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for the google optimize module.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritDoc}
   */
  protected function getEditableConfigNames() {
    return ['google_optimize_js.settings'];
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'google_optimize_js_settings';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('google_optimize_js.settings');
    $form['instructions'] = [
      '#title' => $this->t('Important information'),
      '#type' => 'fieldset',
    ];

    $form['instructions']['page_cache_disclaimer'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('Saving this form will invalidate ALL cached pages and may momentarily cause perceivable performance degradation.'),
      '#attributes' => [
        'class' => [
          'messages',
          'messages--warning',
        ],
      ],
    ];

    $form['settings'] = [
      '#title' => $this->t('Settings'),
      '#type' => 'fieldset',
    ];
    $form['settings']['container'] = [
      '#title' => $this->t('Container Id'),
      '#description' => $this->t('The container ID should take the form of "GTM-XXXXXXX" or "OPT-XXXXXXX.'),
      '#type' => 'textfield',
      '#default_value' => $config->get('container'),
      '#maxlength' => 500,
      '#pattern' => '^(GTM|OPT)-[a-zA-Z0-9]{7}$',
      '#required' => TRUE,
    ];
    $form['settings']['enabled'] = [
      '#title' => $this->t('Enable Google Optimize on specified pages.'),
      '#type' => 'checkbox',
      '#default_value' => $config->get('enabled'),
    ];

    $form['settings']['pages'] = [
      '#title' => $this->t('Pages to add Google Optimize to (leave blank for all pages)'),
      '#type' => 'textarea',
      '#default_value' => $config->get('pages'),
      '#description' => $this->t('Specify pages by using their paths. Enter one path per line. The "*" character is a wildcard. An example path is /user/* for every user page or /node/123. &lt;front&gt; is the front page.'),
      '#states' => [
        'visible' => [
          '[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['settings']['anti_flicker'] = [
      '#title' => $this->t('Anti-flicker snippet'),
      '#type' => 'fieldset',
      '#states' => [
        'visible' => [
          '[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['settings']['anti_flicker']['anti_flicker_timeout'] = [
      '#title' => $this->t('Timeout'),
      '#description' => $this->t('The number of milliseconds to wait before timing out.'),
      '#type' => 'number',
      '#default_value' => $config->get('anti_flicker_timeout'),
      '#min' => 0,
      '#max' => 10000,
      '#states' => [
        'required' => [
          '[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['settings']['anti_flicker']['anti_flicker_pages'] = [
      '#title' => $this->t('Pages to add the anti-flicker snippet on'),
      '#type' => 'textarea',
      '#default_value' => $config->get('anti_flicker_pages'),
      '#description' => $this->t('Specify pages by using their paths. Enter one path per line. The "*" character is a wildcard. An example path is /user/* for every user page or /node/123. &lt;front&gt; is the front page.<br /> <em>Only pages that load optimize will include this snippet.</em>'),
    ];

    $form['settings']['advanced'] = [
      '#title' => $this->t('Advanced'),
      '#type' => 'details',
      '#open' => FALSE,
    ];

    $form['settings']['advanced']['loading_strategy'] = [
      '#title' => $this->t('Loading strategy'),
      '#type' => 'radios',
      '#options' => [
        'synchronous' => $this->t('Synchronous'),
        'asynchronous' => $this->t('Asynchronous'),
      ],
      '#default_value' => $config->get('loading_strategy'),
    ];

    $form['settings']['advanced']['datalayer_js_weight'] = [
      '#title' => $this->t('Override snippet weight'),
      '#type' => 'number',
      '#step' => 0.01,
      '#description' => $this->t('This value should be equal to the weight of the javascript attachment that initializes the datalayer.  Most users should not need to modify this.'),
      '#default_value' => $config->get('datalayer_js_weight'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('google_optimize_js.settings')
      ->set('container', $form_state->getValue('container'))
      ->set('enabled', $form_state->getValue('enabled'))
      ->set('pages', $form_state->getValue('pages'))
      ->set('anti_flicker_timeout', $form_state->getValue('anti_flicker_timeout'))
      ->set('anti_flicker_pages', $form_state->getValue('anti_flicker_pages'))
      ->set('datalayer_js_weight', $form_state->getValue('datalayer_js_weight'))
      ->set('loading_strategy', $form_state->getValue('loading_strategy'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}

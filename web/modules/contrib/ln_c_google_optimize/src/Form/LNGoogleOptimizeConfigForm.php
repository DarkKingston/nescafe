<?php

namespace Drupal\ln_c_google_optimize\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class GoogleOptimizeConfigForm.
 */
class LNGoogleOptimizeConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'google_optimize_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ln_c_google_optimize.googleoptimizeconfig');
    $form['enable_google_optimize'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Enable Google Optimize on this site.'),
      '#default_value' => $config->get('enable_google_optimize'),
    ];
    $form['analytics_property_id'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Analytics Property ID'),
      '#description'   => $this->t('Enter Analytics Property ID here'),
      '#maxlength'     => 255,
      '#size'          => 64,
      '#default_value' => $config->get('analytics_property_id'),
    ];

    $form['container_id'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Container ID'),
      '#description'   => $this->t('Enter GTM container ID here'),
      '#maxlength'     => 255,
      '#size'          => 64,
      '#default_value' => $config->get('container_id'),
    ];

    $form['lang_analytics'] = [
      '#type'        => 'details',
      '#title'       => t('Language-based Analytics Property ID & Container ID'),
      '#collapsible' => TRUE,
    ];

    $form['lang_analytics']['lang_analytics_status'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Enable Language-based Analytics Property ID & Container ID.'),
      '#default_value' => $config->get('lang_analytics_status'),
    ];

    $languages = \Drupal::languageManager()->getLanguages();

    foreach ($languages as $lang => $data) {
      $form['lang_analytics']['lang_analytics_id_' . $lang] = [
        '#type'          => 'textfield',
        '#title'         => $data->getName() . ': ' . t('Analytics Property ID'),
        '#description'   => t('Enter Analytics property ID for ' . $data->getName() . ' language here'),
        '#maxlength'     => 255,
        '#size'          => 64,
        '#default_value' => $config->get('lang_analytics_id_' . $lang),
      ];
      $form['lang_analytics']['lang_container_id_' . $lang] = [
        '#type'          => 'textfield',
        '#title'         => $data->getName() . ': ' . t('Container ID'),
        '#description'   => $this->t('Enter GTM container ID here'),
        '#maxlength'     => 255,
        '#size'          => 64,
        '#default_value' => $config->get('lang_container_id_' . $lang),
      ];
    }


    $form['settings'] = [
      '#type'  => 'fieldset',
      '#title' => $this->t('Settings for the Google Optimize page-hiding snippet'),
    ];

    $form['settings']['hide_page_pages'] = [
      '#title'         => $this->t('Pages to add the snippet (leave blank for all pages)'),
      '#type'          => 'textarea',
      '#default_value' => ln_c_google_optimize_hide_page_pages(),
      '#description'   => $this->t('Specify pages by using their paths. Enter one path per line. The \'*\' character is a wildcard. An example path is /user/* for every user page or /node/123. &lt;front&gt; is the front page'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->config('ln_c_google_optimize.googleoptimizeconfig');
    $config->set('enable_google_optimize', $form_state->getValue('enable_google_optimize'))
      ->set('analytics_property_id', $form_state->getValue('analytics_property_id'))
      ->set('lang_analytics_status', $form_state->getValue('lang_analytics_status'))
      ->set('container_id', $form_state->getValue('container_id'))
      ->set('hide_page_pages', $form_state->getValue('hide_page_pages'));

    $languages = \Drupal::languageManager()->getLanguages();

    foreach ($languages as $lang => $data) {
      $config->set('lang_analytics_id_' . $lang, $form_state->getValue('lang_analytics_id_' . $lang));
      $config->set('lang_container_id_' . $lang, $form_state->getValue('lang_container_id_' . $lang));
    }

    $config->save();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ln_c_google_optimize.googleoptimizeconfig',
    ];
  }

}

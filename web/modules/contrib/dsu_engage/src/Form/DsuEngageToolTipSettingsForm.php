<?php

namespace Drupal\dsu_engage\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EngageContactAdminSettingsForm.
 *
 * @package Drupal\dsu_engage\Form
 */

/**
 * Configure example settings for this site.
 */
class DsuEngageToolTipSettingsForm extends ConfigFormBase {

  protected $langManager;

  /**
   * Constructs a EngageToolTip object.
   *
   * @param object $entity_manager
   *   The entity manager.
   * @param object $lang_manager
   *   The language manager.
   */
  public function __construct($lang_manager) {
    $this->langManager = $lang_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dsu_engage_tooltip_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'dsu_engage_tooltip_form.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $languages = $this->langManager->getLanguages();
    $config = $this->config('dsu_engage_tooltip_form.settings');
    $form['batch_details'] = [
      '#type' => 'details',
      '#title' => $this->t('Batch Code details'),
    ];
    $form['barcode_details'] = [
      '#type' => 'details',
      '#title' => $this->t('Bar Code Details'),
    ];
    foreach ($languages as $lang => $data) {
      $form['batch_details']['dsu_engage_tooltip_text_batch_code_' . $lang] = [
        '#type' => 'textfield',
        '#title' => $data->getName() . ': Tooltip Text',
        '#description' => $this->t('Tooltip Text for Batch Code'),
        '#default_value' => $config->get('dsu_engage_tooltip_text_batch_code_' . $lang),
      ];
      $form['batch_details']['dsu_engage_tooltip_image_batch_' . $lang] = [
        '#type' => 'managed_file',
        '#title' => $data->getName() . ': Upload tooltip Image for Batch Code',
        '#required' => FALSE,
        '#upload_location' => 'public://batch_images',
        '#multiple' => TRUE,
        '#default_value' => !empty($config->get('dsu_engage_tooltip_image_batch_' . $lang)) ? explode(',', $config->get('dsu_engage_tooltip_image_batch_' . $lang)['0']) : '',
        '#upload_validators' => ['file_validate_extensions' => ['png jpg jpeg']],
      ];

      $form['barcode_details']['dsu_engage_tooltip_text_bar_code_' . $lang] = [
        '#type' => 'textfield',
        '#title' => $data->getName() . ': Tooltip Text',
        '#description' => $this->t('Tooltip Text for Bar Code'),
        '#default_value' => $config->get('dsu_engage_tooltip_text_bar_code_' . $lang),
      ];
      $form['barcode_details']['dsu_engage_tooltip_image_bar_' . $lang] = [
        '#type' => 'managed_file',
        '#title' => $data->getName() . ': Upload tooltip Image for bar Code',
        '#required' => FALSE,
        '#upload_location' => 'public://bcode_images',
        '#default_value' => !empty($config->get('dsu_engage_tooltip_image_bar_' . $lang)) ? (explode(',', $config->get('dsu_engage_tooltip_image_bar_' . $lang)['0'])) : '',
        '#upload_validators' => ['file_validate_extensions' => ['png jpg jpeg']],
      ];

    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $configSettings = $this->configFactory->getEditable('dsu_engage_tooltip_form.settings');
    // Save details based on language.
    $languages = $this->langManager->getLanguages();
    foreach ($languages as $lang => $data) {
      $configSettings->set('dsu_engage_tooltip_text_batch_code_' . $lang, $form_state->getValue('dsu_engage_tooltip_text_batch_code_' . $lang));
      $configSettings->set('dsu_engage_tooltip_image_batch_' . $lang, $form_state->getValue('dsu_engage_tooltip_image_batch_' . $lang));
      $configSettings->set('dsu_engage_tooltip_text_bar_code_' . $lang, $form_state->getValue('dsu_engage_tooltip_text_bar_code_' . $lang));
      $configSettings->set('dsu_engage_tooltip_image_bar_' . $lang, $form_state->getValue('dsu_engage_tooltip_image_bar_' . $lang));
    }
    $configSettings->save();
    parent::submitForm($form, $form_state);
  }

}

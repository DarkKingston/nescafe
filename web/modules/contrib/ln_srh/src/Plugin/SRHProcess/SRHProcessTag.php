<?php

namespace Drupal\ln_srh\Plugin\SRHProcess;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ln_srh\SRHConstants;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_tag",
 *   field_name = "tag",
 *   label = @Translation("Tag")
 * )
 */

class SRHProcessTag extends SRHProcessTerm {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'srh_source_field' => '',
      'vocabulary_id' => NULL
    ];
  }

  public function getValues($srh_data, $langcode) {
    $values = [
      'vid' => $this->getVocabularyId()
    ];

    if (is_array($srh_data)) {
      $values['name'] = $srh_data['name'] ?? $srh_data['localizedName'] ?? $srh_data['localizedDisplayName'] ?? $srh_data['displayName'] ?? $srh_data['description'] ?? NULL;
      $values[SRHConstants::SRH_RECIPE_EXTERNAL_FIELD] = $srh_data['id'];
    } else {
      $values['name'] = $srh_data;
    }

    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['srh_source_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SRH source field'),
      '#default_value' => $config['srh_source_field']
    ];

    $form['vocabulary_id'] = [
      '#type' => 'select',
      '#options' => $this->getVocabularyOptions(),
      '#title' => $this->t('Vocabulary'),
      '#default_value' => $config['vocabulary_id']
    ];

    return $form;
  }

  protected function getSRHEntityReferenceData($srh_data) {
    $config = $this->getConfiguration();
    $source_field = $config['srh_source_field'] ?? FALSE;
    return $srh_data[$source_field] ?? FALSE;
  }

  /**
   * @return mixed|null
   */
  protected function getVocabularyId() {
    $config = $this->getConfiguration();
    return $config['vocabulary_id'];
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition, $plugin_definition) {
    $target_type = $field_definition->getFieldStorageDefinition()->getSetting('target_type');
    // This formatter is only available for taxonomy terms.
    return  $target_type == 'taxonomy_term';
  }

}

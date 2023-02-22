<?php

namespace Drupal\ln_srh\Plugin\SRHProcess;


use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a SRHProcess generator plugin for integrationParams field type provided by ln_adimo.
 *
 * @SRHProcess(
 *   id = "srh_process_adimo",
 *   field_name = "srh_adimo",
 *   label = @Translation("Adimo")
 * )
 */

class SRHProcessAdimoField extends SRHProcessDefault {

  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $config = $this->getConfiguration();

    if (!\Drupal::service('module_handler')->moduleExists('ln_adimo')) {
      return $form;
    }

    $file = file_get_contents(drupal_get_path('module', 'ln_adimo') . '/integrations.json', FILE_USE_INCLUDE_PATH);
    $json = json_decode($file);
    $options = [];
    foreach ($json->integrations as $integration) {
      array_push($options, $integration->key);
    }

    $form['integration_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Adimo Integration Type Default Value'),
      '#options'       => $options,
      '#default_value' => $config['integration_type'] ?? NULL
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function process(ContentEntityInterface $entity, $srh_data, $field_name) {
    $touchpointID = parent::process($entity, $srh_data, $field_name);
    if (!$touchpointID && $entity->get($field_name)->isEmpty()) {
      return NULL;
    }

    $config = $this->getConfiguration();
    $defaultIntegrationType = $config['integration_type'];

    $fieldValue = ['integrationType' => $defaultIntegrationType];
    if (!$entity->get($field_name)->isEmpty()) {
      $fieldValue = $entity->get($field_name)->getValue()[0];
    }

    if ($touchpointID) {
      if (empty($fieldValue['touchpointID'])) {
        $fieldValue['integrationType'] = $defaultIntegrationType;
      }
      $fieldValue['touchpointID'] = $touchpointID;
    }

    return $fieldValue;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition, $plugin_definition) {
    return $field_definition->getType() == 'integrationParams';
  }

}

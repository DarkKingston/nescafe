<?php

namespace Drupal\ln_srh\Plugin\SRHProcess;


use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_default",
 *   field_name = "default",
 *   label = @Translation("Default")
 * )
 */

class SRHProcessDefault extends SRHProcessBase{

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(){
    return [
      'srh_source_field' => '',
    ];
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

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function process(ContentEntityInterface $entity, $srh_data, $field_name){
    $config = $this->getConfiguration();
    $source_field = $config['srh_source_field'];
    return $srh_data[$source_field] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition, $plugin_definition) {
    $allowed_types = ['string', 'string', 'decimal', 'float', 'integer'];
    return in_array($field_definition->getType(),$allowed_types);
  }

}

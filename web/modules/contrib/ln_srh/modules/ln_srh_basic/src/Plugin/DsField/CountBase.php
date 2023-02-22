<?php

namespace Drupal\ln_srh_basic\Plugin\DsField;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ds\Plugin\DsField\DsFieldBase;


abstract class CountBase extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    $form['singular_label'] = [
      '#type' => 'textfield',
      '#title' => 'Singular label',
      '#default_value' => $config['singular_label'],
      '#required' => TRUE
    ];

    $form['plural_label'] = [
      '#type' => 'textfield',
      '#title' => 'Plural label',
      '#default_value' => $config['plural_label'],
      '#required' => TRUE
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary($settings) {
    $config = $this->getConfiguration();

    $summary = [];

    $summary[] = 'Singular label: ' . $config['singular_label'];
    $summary[] = 'Plural label: ' . $config['plural_label'];

    return $summary;
  }


  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\node\NodeInterface $entity */
    $entity = $this->entity();
    $field_name = $this->getFieldName();
    if($entity->hasField($field_name)){
      $config = $this->getConfiguration();
      return [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#attributes' => ['class' => ['media-count-value']],
        '#value' => $this->formatPlural($entity->get($field_name)->count(), "@count {$config['singular_label']}", "@count {$config['plural_label']}"),
      ];
    }

    return [];
  }

  /*
   * Return the field name to count values
   *
   * @return string
   * */
  public function getFieldName(){
    return '';
  }
}

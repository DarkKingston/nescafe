<?php

namespace Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Tag;

/**
 * Each dynamic datalayer tag will extend this base.
 */
abstract class DatalayerDynamicNameBase extends DatalayerNameBase {

  /**
   * {@inheritdoc}
   */
  public function form(array $element = []) {
    return [
      '#type' => 'textfield',
      '#title' => $this->label(),
      '#default_value' => '',
      '#placeholder' => $this->t('Dynamic calculated tag'),
      '#disabled' => 'disabled',
      '#maxlength' => 255,
      '#description' => $this->description(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function processValue($value) {
    // Overwrite this method in your plugin to calculate dynamic tag value.
    return $value;
  }

}

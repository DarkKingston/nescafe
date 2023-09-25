<?php

namespace Drupal\ln_datalayer\Plugin\AdvancedDatalayer\Tag;

use Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Tag\DatalayerNameBase;

/**
 * Each datasets datalayer tag will extend this base.
 */
abstract class DatalayerDatasetsBase extends DatalayerNameBase {

  /**
   * Generate a form element for this tag.
   *
   * @param array $element
   *   The existing form element to attach to.
   *
   * @return array
   *   The completed form element.
   */
  public function form(array $element = []) {

    $options = \Drupal::service('ln_datalayer.datasets')->getValues($this->pluginId);

    return [
      '#type' => 'select',
      '#title' => $this->label(),
      '#description' => $this->description(),
      '#options' => $options,
      '#empty_option' => $this->t('- Select -'),
      '#empty_value' => '',
      '#default_value' => $this->value(),
      '#required' => $this->required(),
    ];
  }

}

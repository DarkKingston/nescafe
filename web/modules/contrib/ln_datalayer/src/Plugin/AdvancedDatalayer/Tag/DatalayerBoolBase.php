<?php

namespace Drupal\ln_datalayer\Plugin\AdvancedDatalayer\Tag;

use Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Tag\DatalayerNameBase;

/**
 * Each boolean datalayer tag will extend this base.
 */
abstract class DatalayerBoolBase extends DatalayerNameBase {

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

    return [
      '#type' => 'checkbox',
      '#title' => $this->label(),
      '#description' => $this->description(),
      '#default_value' => $this->value(),
      '#required' => $this->required(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function processValue($value) {
    // Return string true or false for bool tags.
    return $value ? 'true' : 'false';
  }

}

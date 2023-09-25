<?php

namespace Drupal\ln_datalayer\Plugin\AdvancedDatalayer\Tag;

use Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Tag\DatalayerNameBase;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * The basic "goLiveDate" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "goLiveDate",
 *   label = @Translation("Go live date"),
 *   description = @Translation("This parameter should contain the go live date of the website"),
 *   group = "siteInformation",
 *   global = TRUE,
 *   required = TRUE,
 *   translatable = FALSE,
 *   show_empty = FALSE,
 *   weight = 16,
 * )
 */
class GoLiveDate extends DatalayerNameBase {

  /**
   * {@inheritDoc}
   */
  public function form(array $element = []) {

    return [
      '#type' => 'date',
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

    if (!empty($value)) {
      $date = new DrupalDateTime($value, 'UTC');
      return $date->format('d/m/Y');
    }

    return $value;
  }

}

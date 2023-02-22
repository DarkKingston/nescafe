<?php

namespace Drupal\advanced_datalayer\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'advanced_datalayer_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "advanced_datalayer_formatter",
 *   module = "advanced_datalayer",
 *   label = @Translation("Empty formatter"),
 *   field_types = {
 *     "advanced_datalayer"
 *   }
 * )
 */
class AdvancedDatalayerFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // Does not actually output anything.
    return [];
  }

}

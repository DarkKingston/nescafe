<?php
/**
 * @file Contains
 *   \Drupal\ln_price_spider\Plugin\Field\FieldFormatter\PriceSpiderFormatter.
 */

namespace Drupal\ln_price_spider\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'field_price_spider' formatter.
 *
 * @FieldFormatter(
 *   id = "price_spider_formatter",
 *   module = "ln_price_spider",
 *   label = @Translation("Price Spider button formatter"),
 *   field_types = {
 *     "field_price_spider"
 *   }
 * )
 */
class PriceSpiderFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $html_id = 'price-spider-' . rand(0, 999999);

    foreach ($items as $delta => $item) {
      // Render each element as markup.
      $element[$delta] = [
        '#theme' => 'price-spider-button',
        '#price_spider_id' => $item->value,
        '#html_id' => $html_id,
        '#attached' => [
          'library' => [
            'ln_price_spider/price-spider-library',
            'ln_price_spider/price-spider-connector',
          ],
        ],
      ];
    }

    return $element;

  }

}

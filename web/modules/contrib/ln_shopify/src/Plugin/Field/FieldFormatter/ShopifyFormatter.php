<?php

namespace Drupal\ln_shopify\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'field_shopify' formatter.
 *
 * @FieldFormatter(
 *   id = "shopify_formatter",
 *   label = @Translation("Shopify Formatter"),
 *   description = @Translation("Shopify Formatter"),
 *   field_types = {
 *     "field_shopify"
 *   }
 * )
 */
class ShopifyFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    // The ProcessedText element already handles cache context & tag bubbling.
    // @see \Drupal\filter\Element\ProcessedText::preRenderText()
    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#type' => 'processed_text',
        '#text' => $item->value,
        '#format' => $item->format,
        '#langcode' => $item->getLangcode(),
      ];
    }

    return $elements;
  }

}

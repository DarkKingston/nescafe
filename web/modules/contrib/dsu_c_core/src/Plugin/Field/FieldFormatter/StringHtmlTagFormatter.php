<?php

namespace Drupal\dsu_c_core\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\StringFormatter;

/**
 * Plugin implementation of the 'string html tag' formatter.
 *
 * @FieldFormatter(
 *   id = "string_html_tag_formatter",
 *   label = @Translation("Plain text whit selected HTML tag"),
 *   field_types = {
 *     "string_html_tag",
 *   }
 * )
 */
class StringHtmlTagFormatter extends StringFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);

    foreach ($elements as $delta => &$element) {
      if($items[$delta]->html_tag){
        $element = [
          '#type' => 'html_tag',
          '#tag' => $items[$delta]->html_tag,
          [$element],
        ];
      }
    }
    return $elements;
  }
}

<?php

namespace Drupal\ln_srh\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'SRH Serving' formatter.
 *
 * @FieldFormatter(
 *   id = "srh_serving_formatter",
 *   label = @Translation("SRH Serving"),
 *   field_types = {
 *     "srh_serving"
 *   }
 * )
 */
class SRHServingFormatter extends FormatterBase{

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $columns = ['number','display_name'];
    foreach ($items as $delta=>$item){
      $elements[$delta] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'class' => ['item-srh-serving']
        ]
      ];
      foreach ($columns as $column){
        $elements[$delta]['children'][$column] = [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#value' => $item->get($column)->getValue(),
        ];
      }
    }
    return $elements;
  }


}

<?php

namespace Drupal\ln_srh\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'SRH Quantity' formatter.
 *
 * @FieldFormatter(
 *   id = "srh_quantity_formatter",
 *   label = @Translation("SRH Quantity"),
 *   field_types = {
 *     "srh_quantity"
 *   }
 * )
 */
class SRHQuantityFormatter extends FormatterBase{

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $columns = ['quantity','display','grams','fraction'];
    foreach ($items as $delta=>$item){
      $elements[$delta] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'class' => ['item-srh-quantity']
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

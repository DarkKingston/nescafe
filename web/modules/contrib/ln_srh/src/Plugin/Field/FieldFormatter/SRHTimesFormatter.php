<?php

namespace Drupal\ln_srh\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'SRH Times' formatter.
 *
 * @FieldFormatter(
 *   id = "srh_times_formatter",
 *   label = @Translation("SRH Times"),
 *   field_types = {
 *     "srh_times"
 *   }
 * )
 */
class SRHTimesFormatter extends FormatterBase{

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $columns = ['total','serving','preparation','cooking','waiting'];
    foreach ($items as $delta=>$item){
      $elements[$delta] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'class' => ['item-srh-times']
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

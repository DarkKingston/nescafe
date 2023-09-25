<?php

namespace Drupal\ln_c_power_reviews\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'field_ln_c_power_rev_widget' widget.
 *
 * @FieldWidget(
 *   id = "field_ln_c_power_rev_widget",
 *   label = @Translation("Power Reviews - Widget"),
 *   description = @Translation("Lightnest Power Reviews  - Widget"),
 *   field_types = {
 *     "field_ln_c_power_rev_type",
 *   },
 *   multiple_values = TRUE,
 * )
 */
class LightnestPowerReviewsWidget extends WidgetBase {

  /**
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   * @param int $delta
   * @param array $element
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $ln_c_power_reviews_page_id = isset($items[$delta]->ln_c_power_reviews_page_id) ? $items[$delta]->ln_c_power_reviews_page_id : '';

    $element['ln_c_power_reviews_page_id'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Power Reviews Page ID'),
      '#description'   => $this->t('Power Reviews Page ID'),
      '#default_value' => $ln_c_power_reviews_page_id,
    ];


    return $element;
  }


}

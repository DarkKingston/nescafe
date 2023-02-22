<?php
/**
 * @file Contains
 *   \Drupal\ln_price_spider\Plugin\Field\FieldWidget\PriceSpiderWidget.
 */

namespace Drupal\ln_price_spider\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'price_spider_widget' widget.
 *
 * @FieldWidget(
 *   id = "price_spider_widget",
 *   module = "ln_price_spider",
 *   label = @Translation("Price Spider button"),
 *   field_types = {
 *     "field_price_spider"
 *   },
 *   multiple_values = TRUE,
 * )
 */
class PriceSpiderWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $value = isset($items[$delta]->value) ? $items[$delta]->value : '';
    $element += [
      '#type'             => 'textfield',
      '#title'            => $this->t('PriceSpider button'),
      '#default_value'    => $value,
      '#size'             => 50,
      '#maxlength'        => 255,
      '#required'         => $element['#required'],
      '#element_validate' => [
        [$this, 'validate'],
      ],
    ];

    return ['value' => $element];
  }

  /**
   * Validate the text field.
   */
  public function validate($element, FormStateInterface $form_state) {
    $value = $element['#value'];
    if (strlen($value) === 0) {
      $form_state->setValueForElement($element, '');

      return;
    }
  }

}


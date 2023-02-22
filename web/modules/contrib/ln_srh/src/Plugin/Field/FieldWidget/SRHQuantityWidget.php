<?php

namespace Drupal\ln_srh\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ln_srh\Plugin\Field\FieldType\SRHQuantity;

/**
 * Plugin implementation of the 'srh_quantity_widget' widget.
 *
 * @FieldWidget(
 *   id = "srh_quantity_widget",
 *   module = "ln_srh",
 *   label = @Translation("SRH Quantity"),
 *   field_types = {
 *     "srh_quantity"
 *   }
 * )
 */
class SRHQuantityWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    /** @var SRHQuantity $item */
    $item = $items[$delta];
    $element['quantity_wrapper'] = [
      '#type' => 'details',
      '#title' => $this->t('Quantity'),
      '#tree' => TRUE,
      'quantity' => [
        '#type' => 'textfield',
        '#title' => $this->t('Quantity'),
        '#default_value' => $item->get('quantity')->getValue(),
        '#required' => $element['#required']
      ],
      'display' => [
        '#type' => 'textfield',
        '#title' => $this->t('Display'),
        '#default_value' => $item->get('display')->getValue()
      ],
      'grams' => [
        '#type' => 'textfield',
        '#title' => $this->t('Grams'),
        '#default_value' => $item->get('grams')->getValue()
      ],
      'fraction' => [
        '#type' => 'textfield',
        '#title' => $this->t('Fraction'),
        '#size' => 3,
        '#maxlength' => 3,
        '#default_value' => $item->get('fraction')->getValue()
      ]
    ];
    return $element;
  }

  public function massageFormValues(array $values, array $form, FormStateInterface $form_state){
    foreach ($values as $delta => &$value) {
      $value['quantity'] = $value['quantity_wrapper']['quantity'];
      $value['display']  = $value['quantity_wrapper']['display'];
      $value['grams']    = $value['quantity_wrapper']['grams'];
      $value['fraction'] = $value['quantity_wrapper']['fraction'];
    }
    return $values;
  }
}

<?php

namespace Drupal\ln_srh\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ln_srh\Plugin\Field\FieldType\SRHServing;

/**
 * Plugin implementation of the 'srh_serving_widget' widget.
 *
 * @FieldWidget(
 *   id = "srh_serving_widget",
 *   module = "ln_srh",
 *   label = @Translation("SRH Serving"),
 *   field_types = {
 *     "srh_serving"
 *   }
 * )
 */
class SRHServingWidget extends WidgetBase{

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    /** @var SRHServing $item */
    $item = $items[$delta];
    $element['details'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Serving'),
      '#tree' => TRUE,
      'number' => [
        '#type' => 'number',
        '#min' => 1,
        '#title' => $this->t('Number'),
        '#default_value' => $item->getNumber(),
        '#required' => $element['#required']
      ],
      'display_name' => [
        '#type' => 'textfield',
        '#title' => $this->t('Display Name'),
        '#default_value' => $item->getDisplayName()
      ]
    ];
    return $element;
  }

  public function massageFormValues(array $values, array $form, FormStateInterface $form_state){
    foreach ($values as $delta => &$value) {
      $value['number'] = $value['details']['number'];
      $value['display_name'] = $value['details']['display_name'] ?? '';
    }
    return $values;
  }


}

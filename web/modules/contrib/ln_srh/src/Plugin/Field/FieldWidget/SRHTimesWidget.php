<?php

namespace Drupal\ln_srh\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ln_srh\Plugin\Field\FieldType\SRHTimes;

/**
 * Plugin implementation of the 'srh_times_widget' widget.
 *
 * @FieldWidget(
 *   id = "srh_times_widget",
 *   module = "ln_srh",
 *   label = @Translation("SRH Times"),
 *   field_types = {
 *     "srh_times"
 *   }
 * )
 */
class SRHTimesWidget extends WidgetBase{

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    /** @var SRHTimes $item */
    $item = $items[$delta];
    $element['times'] = [
      '#type' => 'details',
      '#title' => $this->t('Times'),
      '#tree' => TRUE,
      'total' => [
        '#type' => 'number',
        '#min' => 0,
        '#title' => $this->t('Total'),
        '#default_value' => $item->get('total')->getValue(),
        '#required' => $element['#required']
      ],
      'serving' => [
        '#type' => 'number',
        '#min' => 0,
        '#title' => $this->t('Serving'),
        '#default_value' => $item->get('serving')->getValue(),
        '#required' => $element['#required']
      ],
      'preparation' => [
        '#type' => 'number',
        '#min' => 0,
        '#title' => $this->t('Preparation'),
        '#default_value' => $item->get('preparation')->getValue(),
        '#required' => $element['#required']
      ],
      'cooking' => [
        '#type' => 'number',
        '#min' => 0,
        '#title' => $this->t('Cooking'),
        '#default_value' => $item->get('cooking')->getValue(),
        '#required' => $element['#required']
      ],
      'waiting' => [
        '#type' => 'number',
        '#min' => 0,
        '#title' => $this->t('Waiting'),
        '#default_value' => $item->get('waiting')->getValue(),
        '#required' => $element['#required']
      ],
    ];
    return $element;
  }

  public function massageFormValues(array $values, array $form, FormStateInterface $form_state){
    foreach ($values as $delta => &$value) {
      $value['total']        = $value['times']['total'];
      $value['serving']      = $value['times']['serving'];
      $value['preparation']  = $value['times']['preparation'];
      $value['cooking']      = $value['times']['cooking'];
      $value['waiting']      = $value['times']['waiting'];
    }
    return $values;
  }


}

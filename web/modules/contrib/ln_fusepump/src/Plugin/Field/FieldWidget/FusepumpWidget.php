<?php

namespace Drupal\ln_fusepump\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'fusepump_widget' widget.
 *
 * @FieldWidget(
 *   id = "fusepump_widget",
 *   module = "ln_fusepump",
 *   label = @Translation("WunderMan BuyNow"),
 *   field_types = {
 *     "field_fusepump"
 *   }
 * )
 */
class FusepumpWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $value = isset($items[$delta]->value) ? $items[$delta]->value : '';
    $element += [
      '#type' => 'textfield',
      '#title' => $this->t('Fusepump button'),
      '#default_value' => $value,
      '#size' => 50,
      '#maxlength' => 255,
      '#required' => $element['#required'],
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

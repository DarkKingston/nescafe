<?php


namespace Drupal\ln_quiz\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\StringTextfieldWidget;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'ln_quiz_answers' widget.
 *
 *
 * @FieldWidget(
 *   id = "ln_quiz_answers_widget",
 *   label = @Translation("Quiz Answer Widget"),
 *   field_types = {
 *     "ln_quiz_answers",
 *   },
 * )
 */

class QuizAnswersWidget extends StringTextfieldWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $element['#type'] = 'fieldset';
    $element['#attributes']['class'][] = 'container-inline';
    $element['right'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Is right'),
      '#default_value' => $items[$delta]->right ?? NULL,
      '#weight' => 99,
      '#title_display' => 'before'
    ];
    $element['#base_type'] = $element['value']['#type'];
    $element['value']['#title_display'] = 'none';
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $elements = parent::formMultipleElements( $items,  $form,  $form_state);

    if(isset($elements['add_more'])){
      $elements['add_more']['#value'] = $this->t('Add another answer item');
    }
    return $elements;
  }

}

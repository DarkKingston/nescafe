<?php

namespace Drupal\dsu_c_core\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\StringTextfieldWidget;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'string_html_tag' widget.
 *
 * @FieldWidget(
 *   id = "string_html_tag_widget",
 *   label = @Translation("Textfield with HTML tag selector"),
 *   field_types = {
 *     "string_html_tag"
 *   }
 * )
 */
class StringHtmlTagWidget extends StringTextfieldWidget {
  protected const HTML_TAGS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'];

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $element['#type'] = 'fieldset';
    $element['#attributes']['class'][] = 'container-inline';
    $element['html_tag'] = [
      '#type' => 'select',
      '#title' => $this->t('HTML tag'),
      '#default_value' => $items[$delta]->html_tag ?? NULL,
      '#options' => ['' => $this->t('- None -')] + array_combine(self::HTML_TAGS, self::HTML_TAGS),
      '#weight' => 99,
    ];
    $element['#base_type'] = $element['value']['#type'];
    $element['value']['#title_display'] = 'none';
    return $element;
  }

}

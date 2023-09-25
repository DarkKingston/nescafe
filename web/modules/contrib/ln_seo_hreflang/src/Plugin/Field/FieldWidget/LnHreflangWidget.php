<?php

namespace Drupal\ln_seo_hreflang\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Url;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\link\LinkItemInterface;
use Drupal\link\Plugin\Field\FieldWidget\LinkWidget;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Plugin implementation of the 'ln_hreflang_widget' widget.
 *
 * @FieldWidget(
 *   id = "ln_hreflang_widget",
 *   label = @Translation("Lightnest hreflang"),
 *   field_types = {
 *     "ln_hreflang"
 *   }
 * )
 */
class LnHreflangWidget extends LinkWidget {

  /**
   * Form element validation handler for the 'link' element.
   *
   * Requires the URL value if a lang title was filled in.
   */
  public static function validateLangNoLink(&$element, FormStateInterface $form_state, $form) {
    if ($element['uri']['#value'] === '' && $element['lang']['#value'] !== '') {
      $form_state->setError($element['uri'], t('The @uri field is required when the @lang field is specified.', ['@lang' => $element['lang']['#title'], '@uri' => $element['uri']['#title']]));
    }
  }

  /**
   * Form element validation handler for the 'lang' element.
   *
   * Requires the URL value if a lang was filled in.
   */
  public static function validateLinkNoLang(&$element, FormStateInterface $form_state, $form) {
    if ($element['lang']['#value'] === '' && $element['uri']['#value'] !== '') {
      $form_state->setError($element['lang'], t('The @lang field is required when the @uri field is specified.', ['@lang' => $element['lang']['#title'], '@uri' => $element['uri']['#title']]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    unset($element['title']);

    $element['lang'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Lang'),
      '#default_value' => isset($items[$delta]->lang) ? $items[$delta]->lang : NULL,
      '#maxlength' => 255,
      '#size' => 50,
      '#required' => $element['#required'],
      '#description' => $this->t(
        'The value of the hreflang attribute identifies the language and optionally a region. See <a href="@link" target="_blank">documentation</a> for more information.',
        ['@link' => 'https://developers.google.com/search/docs/advanced/crawling/localized-versions#language-codes']
      ),
    ];

    $element['#element_validate'][] = [static::class, 'validateLangNoLink'];
    $element['#element_validate'][] = [static::class, 'validateLinkNoLang'];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $elements = parent::formMultipleElements($items, $form, $form_state);

    $elements['add_more']['#ajax']['callback'] = [static::class, 'addMoreAjax'];
    return $elements;
  }

  /**
   * Ajax callback for the "Add another item" button.
   *
   * This returns the new page content to replace the page content made obsolete
   * by the form submission.
   */
  public static function addMoreAjax(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();

    // Go one level up in the form, to the widgets container.
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -1));

    // Ensure the widget allows adding additional items.
    if ($element['#cardinality'] != FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED) {
      return;
    }

    //FIX: https://www.drupal.org/project/drupal/issues/2675688
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = \Drupal::service('renderer');
    $display = '';
    $status_messages = ['#type' => 'status_messages'];
    if ($messages = $renderer->renderRoot($status_messages)) {
      $display = $messages;
    }

    // Add a DIV around the delta receiving the Ajax effect.
    $delta = $element['#max_delta'];
    $element[$delta]['#prefix'] = '<div class="ajax-new-content">' . (isset($element[$delta]['#prefix']) ? $element[$delta]['#prefix'] : '');
    $element[$delta]['#suffix'] = (isset($element[$delta]['#suffix']) ? $element[$delta]['#suffix'] : '') . '</div>';

    $element[0]['#prefix'] = (isset($element[0]['#prefix']) ? $element[0]['#prefix'] : '') . $display;

    return $element;
  }

}

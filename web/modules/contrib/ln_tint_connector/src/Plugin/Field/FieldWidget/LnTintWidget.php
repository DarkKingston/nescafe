<?php

namespace Drupal\ln_tint_connector\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ln_tint_connector\LnTintConstants;

/**
 * Defines the 'ln_tint' field widget.
 *
 * @FieldWidget(
 *   id = "ln_tint",
 *   label = @Translation("Tint"),
 *   field_types = {
 *     "serialized_settings_item"
 *   },
 *   multiple_values = FALSE,
 * )
 */
class LnTintWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['value'] = $element;

    $parents = $element['#field_parents'];
    $parents[] = $this->fieldDefinition->getName();
    $selector = $root = array_shift($parents);
    if ($parents) {
      $selector = $root . '[' . implode('][', $parents) . ']';
    }

    $element['value']['tint_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tint ID'),
      '#default_value' => $items[$delta]->tint_id ?? NULL,
      '#required' => TRUE,
      '#description' => "Enter the ID from your Tint account for your brand."
    ];
    $element['value']['mode'] = [
      '#title' => $this->t('Type'),
      '#type' => 'select',
      '#options' => LnTintConstants::TINT_SELECT_OPTIONS,
      '#default_value' => $items[$delta]->mode ?? NULL,
      '#required' => TRUE,
      '#attributes' => ['class' => ['tint-selector']],
      '#description' => "Enter display mode."
    ];
    // For Iframe
    $element['value']['iframe'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Configuration Iframe'),
      '#open' => TRUE,
      '#states' => array(
        'visible' => array(
          "select[name='{$selector}[{$delta}][value][mode]']" => ['value' => LnTintConstants::TINT_SELECT_OPTIONS_IFRAME],
        ),
      ),
    ];
    $element['value']['iframe']['personalization_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Personalization id'),
      '#default_value' => $items[$delta]->iframe['personalization_id'] ?? NULL,
      '#description' => "Enter the Personalization ID from the Tint Personalize editor for your Tint component display."
    ];
    $element['value']['iframe']['pagination_mode'] = [
      '#type' => 'select',
      '#options' => LnTintConstants::TINT_IFRAME_SELECT_OPTIONS,
      '#title' => $this->t('Pagination Mode'),
      '#default_value' => $items[$delta]->iframe['pagination_mode'] ?? NULL,
      '#description' => "Enter pagination mode."
    ];
    $element['value']['iframe']['data_count'] = [
      '#type' => 'number',
      '#title' => $this->t('Data Count'),
      '#field_name' => 'data_count',
      '#min' => 1,
      '#default_value' => $items[$delta]->iframe['data_count'] ?? NULL,
      '#description' => "Enter integer number for data count."
    ];
    $element['value']['iframe']['tags'] = [
      '#type'          => 'textarea',
      '#description'   => $this->t('Tags for Tint'),
      '#title' => $this->t('Tags'),
      '#default_value' => $items[$delta]->iframe['tags'] ?? NULL,
      '#description' => "Enter tags, separated by commas and without whitespace, to filter the TINT feed by specific topics. For example: drink,chocolate"
    ];
    // For Custom
    $element['value']['custom'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Configuration Custom'),
      '#open' => TRUE,
      '#states' => array(
        'visible' => array(
          "select[name='{$selector}[{$delta}][value][mode]']" => ['value' => LnTintConstants::TINT_SELECT_OPTIONS_CUSTOM],
        ),
      ),
    ];
    $element['value']['custom']['pager_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Pager size'),
      '#field_name' => 'pager_size',
      '#min' => 1,
      '#default_value' => $items[$delta]->custom['pager_size'] ?? NULL,
      '#description' => "Enter the integer number of contents per page."
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return $field_definition->getTargetBundle() == 'dsu_tint';
  }

}

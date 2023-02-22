<?php

namespace Drupal\ln_srh_full\Plugin\DsField\Paragraph;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ds\Plugin\DsField\DsFieldBase;
use Drupal\ln_srh_full\SRHFullConstants;

/**
 * Plugin that renders a quantity ingredient
 *
 * @DsField(
 *   id = "srh_quantity_ingredient",
 *   title = @Translation("SRH Quantity ingredient"),
 *   provider = "ln_srh_full",
 *   entity_type = "paragraph",
 *   ui_limit = {"srh_ingredient|*"},
 * )
 */

class SRHQuantityIngredient extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'use_abbreviation' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $config = $this->getConfiguration();
    $elements['use_abbreviation'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use abbreviation'),
      '#default_value' => $config['use_abbreviation'],
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary($settings) {
    $summary = parent::settingsSummary($settings);

    $summary[] = $this->t('Use abbreviation: @value', ['@value' => $settings['use_abbreviation'] ? $this->t('Yes') : $this->t('No')]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $ingredient = $this->entity();
    $configs = $this->getConfiguration();
    /** @var \Drupal\paragraphs\ParagraphInterface $ingredient */
    $ingredient->get(SRHFullConstants::SRH_PARAGRAPH_INGREDIENTS_QUANTITY_FIELD);
    $ingredient->get(SRHFullConstants::SRH_PARAGRAPH_INGREDIENTS_UNIT_TYPE_FIELD);

    if(!$ingredient->get(SRHFullConstants::SRH_PARAGRAPH_INGREDIENTS_QUANTITY_FIELD)->isEmpty()
        && !$ingredient->get(SRHFullConstants::SRH_PARAGRAPH_INGREDIENTS_UNIT_TYPE_FIELD)->isEmpty()
        && $ingredient->get(SRHFullConstants::SRH_PARAGRAPH_INGREDIENTS_UNIT_TYPE_FIELD)->entity){

      $build = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#attributes' => [
          'class' => ['srh-quantity-ingredients'],
          'data-quantity' => $ingredient->get(SRHFullConstants::SRH_PARAGRAPH_INGREDIENTS_QUANTITY_FIELD)->quantity,
          'data-fraction' => $ingredient->get(SRHFullConstants::SRH_PARAGRAPH_INGREDIENTS_QUANTITY_FIELD)->fraction,
          'data-singular' => $configs['use_abbreviation']
            ? $ingredient->get(SRHFullConstants::SRH_PARAGRAPH_INGREDIENTS_UNIT_TYPE_FIELD)->entity->get(SRHFullConstants::SRH_PARAGRAPH_INGREDIENTS_UNIT_TYPE_TAX_SINGULAR_ABBREV_FIELD)->value
            : $ingredient->get(SRHFullConstants::SRH_PARAGRAPH_INGREDIENTS_UNIT_TYPE_FIELD)->entity->label(),
          'data-plural' => $configs['use_abbreviation']
            ? $ingredient->get(SRHFullConstants::SRH_PARAGRAPH_INGREDIENTS_UNIT_TYPE_FIELD)->entity->get(SRHFullConstants::SRH_PARAGRAPH_INGREDIENTS_UNIT_TYPE_TAX_PLURAL_ABBREV_FIELD)->value
            : $ingredient->get(SRHFullConstants::SRH_PARAGRAPH_INGREDIENTS_UNIT_TYPE_FIELD)->entity->get(SRHFullConstants::SRH_PARAGRAPH_INGREDIENTS_UNIT_TYPE_TAX_PLURAL_FIELD)->value
        ],
        '#value' => $ingredient->get(SRHFullConstants::SRH_PARAGRAPH_INGREDIENTS_FULLNAME_FIELD)->value, //Only for not javascript
        '#attached' => [
          'library' => ['ln_srh_full/quantity_ingredients']
        ]
      ];
      $build['#attributes']['data-initial-quantity'] = $build['#attributes']['data-quantity'];
      $build['#attributes']['data-initial-fraction'] = $build['#attributes']['data-fraction'];

      return $build;
    }
    return [];
  }
}

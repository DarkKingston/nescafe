<?php

namespace Drupal\ln_srh_basic\Plugin\DsField\Node;

use Drupal\ds\Plugin\DsField\DsFieldBase;
use Drupal\ln_srh_basic\Plugin\DsField\CountBase;
use Drupal\ln_srh_basic\SRHBasicConstants;

/**
 * Plugin that renders the ingredient count
 *
 * @DsField(
 *   id = "srh_recipe_ingredient_count",
 *   title = @Translation("Ingredient count"),
 *   provider = "ln_srh_basic",
 *   entity_type = "node",
 *   ui_limit = {"srh_recipe|*"},
 * )
 */

class IngredientCount extends CountBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {

    $configuration = [
      'singular_label' => 'Ingredient',
      'plural_label' => 'Ingredients',
    ];

    return $configuration;
  }


  /**
   * {@inheritdoc}
   */
  public function getFieldName() {
    return SRHBasicConstants::SRH_RECIPE_INGREDIENTS_FIELD;
  }
}

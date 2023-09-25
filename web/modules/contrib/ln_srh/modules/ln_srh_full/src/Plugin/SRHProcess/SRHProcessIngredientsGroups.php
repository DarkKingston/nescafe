<?php

namespace Drupal\ln_srh_full\Plugin\SRHProcess;

use Drupal\ln_srh\Plugin\SRHProcess\SRHProcessParagraph;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_ingredients_groups",
 *   field_name = "field_srh_ingredients_groups",
 *   label = @Translation("Ingredients Groups")
 * )
 */

class SRHProcessIngredientsGroups extends SRHProcessParagraph {

  protected function getSRHEntityReferenceData($srh_data) {
    return $srh_data['ingredientGroups'] ?? [];
  }

  public function getValues($srh_data, $langcode) {
    return [
      'type' => 'srh_ingredient_group',
      'field_c_title' => $srh_data['name'] ?? '',
      'field_srh_recipe_ingredients_ids' => $srh_data['recipeIngredientIds'] ?? []
    ];
  }

}

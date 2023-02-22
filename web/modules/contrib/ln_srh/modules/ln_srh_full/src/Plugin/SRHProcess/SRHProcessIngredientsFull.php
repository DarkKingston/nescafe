<?php

namespace Drupal\ln_srh_full\Plugin\SRHProcess;

use Drupal\ln_srh_standard\Plugin\SRHProcess\SRHProcessIngredientsStandard;
use Drupal\ln_srh_basic\Plugin\SRHProcess\SRHProcessGallery;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_ingredients_full",
 *   field_name = "field_srh_ingredients",
 *   label = @Translation("Ingredients Full")
 * )
 */

class SRHProcessIngredientsFull extends SRHProcessIngredientsStandard {

  public function getValues($srh_data, $langcode) {
    $values = parent::getValues($srh_data, $langcode);

    if (!empty($srh_data['media'])) {
      /** @var SRHProcessGallery $srhProcessMediaPlugin */
      $srhProcessMediaPlugin = $this->srhProcessManager->createInstance('srh_process_gallery', ['multiple' => FALSE]);
      $ingredient = $this->paragraphStorage->create($values);
      $values['field_srh_media'] = $srhProcessMediaPlugin->process($ingredient, $srh_data, 'field_srh_media');
    }

    $values += [
      'field_srh_recipe_ingredient_id' => $srh_data['recipeIngredientId'] ?? $srh_data['complementIngredientId'] ?? '',
      'field_srh_gtin' => $srh_data['GTIN'] ?? '',
      'field_srh_nutritional_db_id' => $srh_data['nutritionalDatabaseID'] ?? '',
    ];
    return $values;
  }

}

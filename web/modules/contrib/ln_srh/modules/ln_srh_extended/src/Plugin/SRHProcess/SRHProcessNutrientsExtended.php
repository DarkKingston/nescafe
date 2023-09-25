<?php

namespace Drupal\ln_srh_extended\Plugin\SRHProcess;

use Drupal\ln_srh\SRHConstants;
use Drupal\ln_srh_standard\Plugin\SRHProcess\SRHProcessNutrientsStandard;

/**
 * Provides a SRHProcess generator plugin.
 *
 * @SRHProcess(
 *   id = "srh_process_nutrients_extended",
 *   field_name = "field_srh_nutrients",
 *   label = @Translation("Nutrients extended")
 * )
 */

class SRHProcessNutrientsExtended extends SRHProcessNutrientsStandard {

  public function getValues($srh_data, $langcode) {
    $quantity = isset($srh_data['quantity']) ? $srh_data['quantity'] : NULL;
    return [
      'type' => 'srh_nutrient',
      'field_srh_percentage' => $srh_data['percentage'] ?? '',
      'field_srh_nutrient' => $this->provideNutrientTerm($srh_data,$langcode),
      'field_srh_nutrient_quantity' => $quantity,
    ];
  }

  /**
   * @param $srh_nutrient
   * @param $langcode
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\taxonomy\TermInterface|mixed|null
   */
  private function provideNutrientTerm($srh_nutrient,$langcode){
    if(isset($srh_nutrient['id']) && !empty($srh_nutrient['id'])){
      $values = [
        'vid' => 'srh_nutrient',
        'name' => $srh_nutrient['name'] ?? $srh_nutrient['id'],
        SRHConstants::SRH_RECIPE_EXTERNAL_FIELD => $srh_nutrient['id'],
        'field_srh_unit' => $srh_nutrient['unit'] ?? '',
        'field_srh_unit_type' => $this->provideUnitTypeTerm($srh_nutrient,$langcode),
        'field_srh_display_name' => $srh_nutrient['displayName'] ?? $srh_nutrient['name'] ?? '',
      ];
      return $this->srhUtils->provideTerm($values,$langcode);
    }
    return NULL;
  }

  /**
   * @param $srh_nutrient
   * @return \Drupal\Core\Entity\EntityInterface|mixed|null
   */
  private function provideUnitTypeTerm($srh_nutrient,$langcode){
    if(isset($srh_nutrient['unitType']['id']) && !empty($srh_nutrient['unitType']['id'])){
      $values = [
        'vid' => 'srh_nutrient_unit_type',
        'name' => $srh_nutrient['unitType']['singularName'] ?? $srh_nutrient['unitType']['localizedName'] ?? $srh_nutrient['unitType']['id'],
        'description' => $srh_nutrient['unitType']['description'] ?? '',
        'field_srh_id' => $srh_nutrient['unitType']['id'],
        'field_srh_plural_name' => $srh_nutrient['unitType']['pluralName'] ?? '',
        'field_srh_abbreviation' => $srh_nutrient['unitType']['singularAbbreviation'] ?? '',
        'field_srh_plural_abbreviation' => $srh_nutrient['unitType']['pluralAbbreviation'] ?? '',
      ];
      return $this->srhUtils->provideTerm($values,$langcode);
    }
    return NULL;
  }

}

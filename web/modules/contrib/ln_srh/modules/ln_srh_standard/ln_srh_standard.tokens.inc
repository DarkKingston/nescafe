<?php

use Drupal\node\NodeInterface;
use Drupal\ln_srh\SRHConstants;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\ln_srh_standard\SRHStandardConstants;

/**
 * Implements hook_token_info().
 */
function ln_srh_standard_token_info(){
  $info['tokens']['node']['srh_calories'] = [
    'name' => t('SRH Calories'),
    'description' => t('SRH Calories'),
  ];
  $info['tokens']['node']['srh_cholesterol'] = [
    'name' => t('SRH Cholesterol'),
    'description' => t('SRH Cholesterol'),
  ];
  $info['tokens']['node']['srh_protein'] = [
    'name' => t('SRH Protein'),
    'description' => t('SRH Protein'),
  ];
  $info['tokens']['node']['srh_carbohydrates'] = [
    'name' => t('SRH Carbohydrate'),
    'description' => t('SRH Carbohydrate'),
  ];
  $info['tokens']['node']['srh_sugar'] = [
    'name' => t('SRH Sugar'),
    'description' => t('SRH Sugar'),
  ];
  $info['tokens']['node']['srh_fiber'] = [
    'name' => t('SRH Fiber'),
    'description' => t('SRH Fiber'),
  ];
  $info['tokens']['node']['srh_sodium'] = [
    'name' => t('SRH Sodium'),
    'description' => t('SRH Sodium'),
  ];
  $info['tokens']['node']['srh_fat'] = [
    'name' => t('SRH Fat'),
    'description' => t('SRH Fat'),
  ];
  $info['tokens']['node']['srh_saturated_fat'] = [
    'name' => t('SRH Saturated Fat'),
    'description' => t('SRH Saturated Fat'),
  ];
  $info['tokens']['node']['srh_unsatured_fat'] = [
    'name' => t('SRH Unsatured Fat'),
    'description' => t('SRH Unsatured Fat'),
  ];
  $info['tokens']['node']['srh_trans_fat'] = [
    'name' => t('SRH Trans Fat'),
    'description' => t('SRH Trans Fat'),
  ];
  return $info;
}

/**
 * Implements hook_tokens().
 */
function ln_srh_standard_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];
  $macroNutrients = SRHStandardConstants::SRH_MACRONUTRIENTS;
  if ($type == 'node' && isset($data['node']) && !empty($data['node'])) {
    /** @var NodeInterface $node */
    $node = $data['node'];
    if ($node->getType() == SRHConstants::SRH_RECIPE_BUNDLE) {
      foreach ($tokens as $name => $original) {
        switch ($name) {
          case 'srh_calories':
            $replacements[$original] = _ln_srh_standard_nutrient($node,$macroNutrients['calories']);
            break;
          case 'srh_cholesterol':
            $replacements[$original] = _ln_srh_standard_nutrient($node,$macroNutrients['cholesterol']);
            break;
          case 'srh_protein':
            $replacements[$original] = _ln_srh_standard_nutrient($node,$macroNutrients['protein']);
            break;
          case 'srh_carbohydrates':
            $replacements[$original] = _ln_srh_standard_nutrient($node,$macroNutrients['carbohydrates']);
            break;
          case 'srh_sugar':
            $replacements[$original] = _ln_srh_standard_nutrient($node,$macroNutrients['sugar']);
            break;
          case 'srh_fiber':
            $replacements[$original] = _ln_srh_standard_nutrient($node,$macroNutrients['fiber']);
            break;
          case 'srh_sodium':
            $replacements[$original] = _ln_srh_standard_nutrient($node,$macroNutrients['sodium']);
            break;
          case 'srh_fat':
            $replacements[$original] = _ln_srh_standard_nutrient($node,$macroNutrients['fat']);
            break;
          case 'srh_saturated_fat':
            $replacements[$original] = _ln_srh_standard_nutrient($node,$macroNutrients['saturated_fat']);
            break;
          case 'srh_unsatured_fat':
            $replacements[$original] = _ln_srh_standard_nutrient($node,$macroNutrients['unsatured_fat']);
            break;
          case 'srh_trans_fat':
            $replacements[$original] = _ln_srh_standard_nutrient($node,$macroNutrients['trans_fat']);
            break;
        }
      }
    }
  }

  return $replacements;
}

function _ln_srh_standard_nutrient(NodeInterface $recipe, $nutrientId){
  $score = '';
  if($recipe->hasField(SRHStandardConstants::SRH_RECIPE_NUTRIENTS_FIELD) && !$recipe->get(SRHStandardConstants::SRH_RECIPE_NUTRIENTS_FIELD)->isEmpty()){
    $nutrients = $recipe->get(SRHStandardConstants::SRH_RECIPE_NUTRIENTS_FIELD)->referencedEntities();
    /** @var \Drupal\paragraphs\ParagraphInterface $nutrient */
    foreach ($nutrients as $nutrient){
      if($nutrient->hasField(SRHStandardConstants::SRH_NUTRIENT_TERM_FIELD) && !$nutrient->get(SRHStandardConstants::SRH_NUTRIENT_TERM_FIELD)->isEmpty()){
        /** @var \Drupal\taxonomy\TermInterface $nutrientTerm */
        $nutrientTerm = $nutrient->get(SRHStandardConstants::SRH_NUTRIENT_TERM_FIELD)->entity;
        if($nutrientTerm->hasField(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD) && !$nutrientTerm->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->isEmpty()){
          if($nutrientTerm->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->getString() == $nutrientId){
            if($nutrient->hasField(SRHStandardConstants::SRH_NUTRIENT_QUANTITY_FIELD) && !$nutrient->get(SRHStandardConstants::SRH_NUTRIENT_QUANTITY_FIELD)->isEmpty()){
              return round((float) $nutrient->get(SRHStandardConstants::SRH_NUTRIENT_QUANTITY_FIELD)->getString());
            }
          }
        }
      }
    }
  }
  return $score;
}

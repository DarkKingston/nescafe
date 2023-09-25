<?php

namespace Drupal\ln_srh_menuiq;


interface SRHMyMenuIQConstants
{
  const SRH_RECIPE_SIDEDISHES_FIELD = 'field_srh_sidedishes';
  const SRH_RECIPE_MENUIQSCORE_FIELD = 'field_srh_menuiqscore';
  const SRH_RECIPE_NUTRIENTS_FIELD = 'field_srh_nutrients';
  const SRH_RECIPE_NUTRITIONAL_TIPS_FIELD = 'field_srh_nutritional_tips';
  const SRH_TIP_TITLE_FIELD = 'field_c_title';
  const SRH_NUTRIENT_TERM_FIELD = 'field_srh_nutrient';
  const SRH_NUTRIENT_QUANTITY_FIELD = 'field_srh_nutrient_quantity';
  const SRH_PARAGRAPH_SIDEDISH_BUNDLE = 'srh_sidedish';
  const SRH_RECIPE_GALLERY_FIELD = 'field_srh_media_gallery';
  const SRH_ASSOCIATION_TYPE_FIELD = 'field_srh_association_type';
  const SRH_SIDEDISH_RECIPE_FIELD = 'field_srh_recipe';
  const SRH_SIDEDISH_TITLE_FIELD = 'field_c_title';
  const SRH_RECIPE_DIFFICULTY_FIELD = 'field_srh_difficulty';
  const SRH_RECIPE_STEPS_FIELD = 'field_srh_steps';
  const SRH_STEP_DURATION_FIELD = 'field_srh_duration';
  const SRH_ASSOCIATION_TYPE_RECIPE = 1;
  const SRH_ASSOCIATION_TYPE_COMPLEMENT = 2;
  const SRH_SIDEDISHES_TYPE_FIELD = 'field_srh_sidedish_type';
  const SRH_SIDEDISHES_SCORE_FIELD = 'field_srh_percentage';
  const SRH_RECIPE_ADIMO_FIELD = 'field_srh_adimo';
  const SRH_SIDEDISHES_TYPES = ['1'=>'starter','2'=>'sidedish','3'=>'dessert'];
  const SRH_MAIN_COURSE_TAG = 61;
  const MYMENUIQ_ENERGY_NUTRIENTS = ['97' => ['name'=>'fat'],'95' => ['name'=>'carbohydrates'],'96' => ['name'=>'protein']];
  const MYMENUIQ_MEALSCORE_NUTRIENTS = [
    '98' => ['name' => 'fiberGrams', 'value' => 0],
    '100' => ['name' => 'sodiumMilligrams', 'value' => 0],
    '109' => ['name' => 'potassiumMilligrams', 'value' => 0],
    '97' => ['name' => 'fatTotalGrams', 'value' => 0],
    '105' => ['name' => 'calciumMilligrams', 'value' => 0],
    '102' => ['name' => 'fatSaturatedGrams', 'value' => 0],
    '106' => ['name' => 'ironMilligrams', 'value' => 0],
    '115' => ['name' => 'addedSugarsTeaSpoons', 'value' => 0],
    '94' => ['name' => 'kcal', 'value' => 0],
    '96' => ['name' => 'proteinGrams', 'value' => 0]
  ];
}

<?php

namespace Drupal\ln_srh_standard;


interface SRHStandardConstants {
  const SRH_MACRONUTRIENTS = [
    'calories' => '94',
    'cholesterol' => '101',
    'protein' => '96',
    'carbohydrates' => '95',
    'sugar' => '99',
    'fiber' => '98',
    'sodium' => '100',
    'fat' => '97',
    'saturated_fat' => '102',
    'unsatured_fat' => '103',
    'trans_fat' => '104',
  ];
  const SRH_RECIPE_NUTRIENTS_FIELD = 'field_srh_nutrients';
  const SRH_NUTRIENT_TERM_FIELD = 'field_srh_nutrient';
  const SRH_NUTRIENT_QUANTITY_FIELD = 'field_srh_nutrient_quantity';
}

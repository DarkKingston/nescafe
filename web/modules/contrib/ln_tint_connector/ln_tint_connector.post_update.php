<?php

/**
 * @file
 * Post update functions for ln_tint_connector module.
 */

use Drupal\ln_tint_connector\LnTintConstants;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\field\Entity\FieldConfig;

/**
 * Migrate old field values to news
 */
function ln_tint_connector_post_update_refactor_fields_migrate(&$sandbox = NULL) {
  $mapping_fields = [
    'field_c_advanced_title' => [
      'value' => 'field_headline',
      'html_tag' => 'field_dsu_tint_title_style'
    ],
    'field_c_text' => 'field_intro_text',
    'field_c_settings' => [
      'value' => [
        'tint_id' => 'field_data_id',
        'personalization_id' => 'field_data_personalization_id',
        'pagination_mode' => [
          LnTintConstants::TINT_IFRAME_SELECT_OPTION_CLICKFORME => 'field_data_clickformore',
          LnTintConstants::TINT_IFRAME_SELECT_OPTIONS_INFINITE => 'field_data_infinitescroll'
        ],
        'tags' => 'field_data_tags'
      ],
    ]
  ];
  foreach(Paragraph::loadMultiple() as $paragraph){
    if($paragraph->bundle() == 'dsu_tint'){
      $updated = FALSE;
      foreach ($mapping_fields as $new_field => $mapping_field){
        if($paragraph->hasField($new_field)){
          if(is_array($mapping_field)){
            foreach ($mapping_field as $property => $mapping_property){
              if(is_array($mapping_property)){
                $array_properties = array();
                foreach ($mapping_property as $property_array => $mapping_property_array){
                  if( is_array($mapping_property_array) ){
                      foreach ($mapping_property_array as $property_array_selects => $mapping_property_array_selects){
                        if($paragraph->hasField($mapping_property_array_selects) && !$paragraph->get($mapping_property_array_selects)->isEmpty() && $paragraph->get($mapping_property_array_selects)->value == "true"){
                          $array_properties['iframe'][$property_array] = $property_array_selects;
                        }
                      }
                  }else{
                    if($paragraph->hasField($mapping_property_array) && !$paragraph->get($mapping_property_array)->isEmpty()){
                      //Out fieldset for tint_id
                      if($property_array == 'tint_id'){
                        $array_properties[$property_array] = $paragraph->get($mapping_property_array)->value;
                      }else{
                        $array_properties['iframe'][$property_array] = $paragraph->get($mapping_property_array)->value;
                      }
                    }
                  }
                }
                if( !empty($array_properties) ){
                  //Force the iframe, because it must be iframe.
                  $array_properties['mode'] = LnTintConstants::TINT_SELECT_OPTIONS_IFRAME;

                  $paragraph->get($new_field)->$property = $array_properties;
                  $updated = TRUE;
                }
              }else{
                if($paragraph->hasField($mapping_property) && !$paragraph->get($mapping_property)->isEmpty()){
                  $paragraph->get($new_field)->$property = $paragraph->get($mapping_property)->value;
                  $updated = TRUE;
                }
              }
            }
          }else{
            $paragraph->get($new_field)->setValue($paragraph->get($mapping_field)->getValue());
            $updated = TRUE;
          }
        }
      }
      if($updated){
        $paragraph->save();
      }
    }
  }

  //Removes old fields for dsu_tint.
  foreach (['field_headline', 'field_dsu_tint_title_style', 'field_intro_text', 'field_data_columns', 'field_data_expand', 'field_block_height', 'field_data_id', 'field_data_personalization_id', 'field_data_clickformore', 'field_data_infinitescroll', 'field_data_tags' ] as $field_name) {
    if($field = FieldConfig::loadByName('paragraph', 'dsu_tint', $field_name)) {
      $field->delete();
    }
  }

}

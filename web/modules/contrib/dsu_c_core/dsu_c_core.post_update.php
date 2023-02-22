<?php

/**
 * @file
 * Post update functions for dsu_c_image module.
 */

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Migrate old field values to news
 */
function dsu_c_core_post_update_refactor_fields_migrate(&$sandbox = NULL) {
  $mapping_fields = [
    'section_id' => 'field_section_id',
    'css_class' => 'field_css_class',
    'classy' => [
      'field_class',
      'field_classy_paragraph_style',
      'field_classy_paragraph_txt_style',
    ]
  ];
  foreach(Paragraph::loadMultiple() as $paragraph){
    $updated = FALSE;
    foreach ($mapping_fields as $new_field => $mapping_field){
      if($paragraph->hasField($new_field)){
        if(is_array($mapping_field)){
          foreach ($mapping_field as $mapping_single_field){
            if($paragraph->hasField($mapping_single_field) && !$paragraph->get($mapping_single_field)->isEmpty()){
              $paragraph->get($new_field)->setValue(array_merge($paragraph->get($new_field)->getValue(), $paragraph->get($mapping_single_field)->getValue()));
              $updated = TRUE;
            }
          }
        }else if($paragraph->hasField($mapping_field)){
          if(substr($new_field, 0, 6) === 'field_'){
            $paragraph->get($new_field)->setValue($paragraph->get($mapping_field)->getValue());
            $updated = TRUE;
          }else{
            $paragraph->$new_field = $paragraph->get($mapping_field)->value;
            $updated = TRUE;
          }
        }
      }
    }
    if($updated){
      $paragraph->save();
    }
  }


  //Removes old fields.
  foreach (['field_section_id', 'field_css_class', 'field_class', 'field_classy_paragraph_txt_style'] as $field_name) {
    if ($field_storage = FieldStorageConfig::loadByName('paragraph', $field_name)) {
      foreach ($field_storage->getBundles() as $bundle){
        if($field = FieldConfig::loadByName('paragraph', $bundle, $field_name)) {
          $field->delete();
        }
      }
    }
  }
}

/**
 * Migrate old field values to news
 */
function dsu_c_core_post_update_refactor_classy_remove(&$sandbox = NULL) {

  foreach(Paragraph::loadMultiple() as $paragraph){
    $updated = FALSE;

    //Removes old fields.
    if( $classies = $paragraph->get('classy')->getValue() ){
      $index = count($classies) - 1;
      foreach ( array_reverse($classies) as $key => $classy){
        foreach (['dsu_classy_text_dark_background', 'dsu_classy_text_image_background', 'dsu_classy_text_light_background'] as $field_name) {
          if($classy['target_id'] == $field_name){
            $paragraph->get('classy')->removeItem( $index - $key);
            $updated = TRUE;
          }
        }
      }
    }
    if($updated){
      $paragraph->save();
    }
  }
}

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
function dsu_c_image_post_update_refactor_fields_migrate(&$sandbox = NULL) {
  $mapping_fields = [
    'field_c_advanced_title' => [
      'value' => 'field_c_title',
      'html_tag' => 'field_c_image_title_style'
    ],
    'field_c_advanced_subtitle' => 'field_c_image_subheading',
    'field_c_text' => 'field_c_image_summary_text'
  ];
  foreach(Paragraph::loadMultiple() as $paragraph){
    if($paragraph->bundle() == 'c_image'){
      $updated = FALSE;
      foreach ($mapping_fields as $new_field => $mapping_field){
        if($paragraph->hasField($new_field)){
          if(is_array($mapping_field)){
            foreach ($mapping_field as $property => $mapping_property){
              if($paragraph->hasField($mapping_property) && !$paragraph->get($mapping_property)->isEmpty()){
                $paragraph->get($new_field)->$property = $paragraph->get($mapping_property)->value;
                $updated = TRUE;
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

  //Removes old fields and groups.
  foreach (['field_classy_paragraph_style', 'field_c_image_summary_text', 'field_button_color', 'field_horizontal_aligment', 'field_c_image_subheading', 'field_text_horizontal_alignment', 'field_text_vertical_alignment', 'field_c_title', 'field_c_image_title_style', 'field_position'] as $field_name) {
    if($field = FieldConfig::loadByName('paragraph', 'c_image', $field_name)) {
      $field->delete();
    }
  }

  if(\Drupal::service('module_handler')->moduleExists('field_group')){
    foreach (['group_cta_button', 'group_position'] as $group_name){
      if($group = field_group_load_field_group($group_name, 'paragraph', 'c_image', 'form', 'default')){
        field_group_delete_field_group($group);
      }
    }
  }
}

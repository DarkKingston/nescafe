<?php

/**
 * @file
 * Post update functions for dsu_c_sideimagetext module.
 */

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Migrate old field values to news
 */
function dsu_c_sideimagetext_post_update_refactor_fields_migrate(&$sandbox = NULL) {
  $mapping_fields = [
    'field_c_advanced_title' => [
      'value' => 'field_c_title',
      'html_tag' => 'field_c_image_text_title_style'
    ],
    'field_c_advanced_subtitle' => 'field_c_sideimagetext_subheading',
    'field_c_text' => 'field_c_sideimagetext_summary',
  ];
  foreach(Paragraph::loadMultiple() as $paragraph){
    if($paragraph->bundle() == 'c_sideimagetext'){
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
      if($paragraph->hasField('field_image_position') && !$paragraph->get('field_image_position')->isEmpty()){
        $paragraph->get('classy')[] = ['target_id' => "dsu_c_sideimagetext_image_{$paragraph->get('field_image_position')->value}"];
      }
      if($updated){
        $paragraph->save();
      }
    }
  }

  //Removes old fields and groups.
  foreach (['field_c_title', 'field_c_image_text_title_style', 'field_c_sideimagetext_subheading', 'field_c_sideimagetext_summary', 'field_text_horizontal_alignment', 'field_text_vertical_alignment', 'field_button_color', 'field_horizontal_aligment', 'field_vertical_alignment', 'field_image_position', 'field_classy_paragraph_style', 'field_background_image'] as $field_name) {
    if($field = FieldConfig::loadByName('paragraph', 'c_sideimagetext', $field_name)) {
      $field->delete();
    }
  }

  if(\Drupal::service('module_handler')->moduleExists('field_group')){
    foreach (['group_cta_button', 'group_position'] as $group_name){
      if($group = field_group_load_field_group($group_name, 'paragraph', 'c_sideimagetext', 'form', 'default')){
        field_group_delete_field_group($group);
      }
    }
  }
}

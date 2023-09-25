<?php

/**
 * @file
 * Post update functions for dsu_c_slider module.
 */

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\field\Entity\FieldConfig;

/**
 * Migrate old field values to news
 */
function dsu_c_slider_post_update_refactor_fields_migrate(&$sandbox = NULL) {
  $mapping_fields_slider = [
    'field_c_subitems' => 'field_c_slide',
  ];
  $mapping_fields = [
    'field_c_advanced_title' => [
      'value' => 'field_c_title',
      'html_tag' => 'field_c_slide_title_style'
    ],
    'field_c_text' => 'field_slide_description',
  ];
  foreach(Paragraph::loadMultiple() as $paragraph){
    if($paragraph->bundle() == 'c_slider'){
      $updated = FALSE;
      foreach ($mapping_fields_slider as $new_field => $mapping_field){
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
    }elseif($paragraph->bundle() == 'c_slide'){
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
        if($paragraph->hasField('field_slide_content_position') && !$paragraph->get('field_slide_content_position')->isEmpty()){
          $mapping_class = NULL;
          switch($paragraph->get('field_slide_content_position')->value){
            case 'above_center':
              $mapping_class = 'dsu_text_position_top_center';
              break;
            case 'above_left':
              $mapping_class = 'dsu_text_position_top_left';
              break;
            case 'above_right':
              $mapping_class = 'dsu_text_position_top_right';
              break;
            case 'bellow_center':
              $mapping_class = 'dsu_text_position_bottom_center';
              break;
            case 'bellow_left':
              $mapping_class = 'dsu_text_position_bottom_left';
              break;
            case 'bellow_right':
              $mapping_class = 'dsu_text_position_bottom_right';
              break;
            case 'over_center':
              $mapping_class = 'dsu_text_position_middle_center';
              break;
            case 'over_right':
              $mapping_class = 'dsu_text_position_middle_right';
              break;
            case 'over_left':
              $mapping_class = 'dsu_text_position_middle_left';
              break;
          }
          $paragraph->get('classy')->setValue($mapping_class);
          $updated = TRUE;
        }
      }
      if($updated){
        $paragraph->save();
      }
    }
  }

  //Removes old fields.
  foreach (['field_c_slide', 'field_classy_paragraph_style'] as $field_name) {
    if($field = FieldConfig::loadByName('paragraph', 'c_slider', $field_name)) {
      $field->delete();
    }
  }
  foreach (['field_c_title','field_c_slide_title_style', 'field_slide_description', 'field_button_color', 'field_gradient_option', 'field_slide_content_position'] as $field_name) {
    if($field = FieldConfig::loadByName('paragraph', 'c_slide', $field_name)) {
      $field->delete();
    }
  }

}

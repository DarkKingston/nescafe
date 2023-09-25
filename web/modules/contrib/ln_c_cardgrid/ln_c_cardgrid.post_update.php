<?php

/**
 * @file
 * Post update functions for ln_c_cardgrid module.
 */

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\field\Entity\FieldConfig;
use Drupal\classy_paragraphs\Entity\ClassyParagraphsStyle;

/**
 * Migrate old field values to news
 */
function ln_c_cardgrid_post_update_refactor_fields_migrate(&$sandbox = NULL) {
  $mapping_fields_card = [
    'field_c_advanced_title' => [
      'value' => 'field_ln_c_card_title',
      'html_tag' => 'field_c_cardgrid_title_style'
    ],
  ];
  $mapping_fields_card_item = [
    'field_c_advanced_title' => [
      'value' => 'title',
    ],
    'field_c_advanced_subtitle' => [
      'value' => 'field_teaser_subtitle',
    ],
    'field_c_text' => 'body',
    'field_c_image' => 'field_image',
    'field_c_link' => 'field_teaser_link',
  ];
  //Migrate old fields
  foreach(Paragraph::loadMultiple() as $paragraph){
    $updated = FALSE;
    if($paragraph->bundle() == 'ln_c_cardgrid'){
      foreach ($mapping_fields_card as $new_field => $mapping_field){
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

      if($paragraph->hasField('field_ln_c_card_carousels') && !$paragraph->get('field_ln_c_card_carousels')->isEmpty()){
        $paragraph->get('field_c_subitems')->setValue($paragraph->get('field_ln_c_card_carousels')->getValue());
        $paragraph->get('field_ln_c_card_carousels')->setValue([]);
        $updated = TRUE;
      }
      if($paragraph->hasField('field_ln_c_grid_card_style') && !$paragraph->get('field_ln_c_grid_card_style')->isEmpty()){
        $view_mode = NULL;
        switch($paragraph->get('field_ln_c_grid_card_style')->target_id){
          case 'ln_c_gridcard_basic_style_1':
            $view_mode = 'bottom_text_box';
            break;
          case 'ln_c_gridcard_basic_style_2':
            $view_mode = 'extended_card';
            break;
          case 'ln_c_gridcard_basic_style_3':
            $view_mode = 'extended_card_title_image';
            break;
          case 'ln_c_gridcard_hover_box_1':
            $view_mode = 'hover_card_box';
            break;
          case 'ln_c_gridcard_hover_box_2':
            $view_mode = 'hover_card_box_profile';
            break;
          case 'ln_c_gridcard_quick_info_box':
            $view_mode = 'full_color_box';
            break;
          case 'ln_c_gridcard_rollover':
            $view_mode = 'rollover_card';
            break;
        }
        $paragraph->get('field_c_cardgrid_view_mode')->value = $view_mode;
        $updated = TRUE;
      }

      if($paragraph->hasField('field_number_of_columns') && !$paragraph->get('field_number_of_columns')->isEmpty()){
        $paragraph->get('classy')[] = ['target_id' => "layout_{$paragraph->get('field_number_of_columns')->value}_col"];
        $updated = TRUE;
      }
    }else if($paragraph->bundle() == 'ln_c_grid_card_item'){
      if($paragraph->hasField('field_ln_c_grid_card_teaser') && !$paragraph->get('field_ln_c_grid_card_teaser')->isEmpty() && ($teaser = $paragraph->get('field_ln_c_grid_card_teaser')->entity)){
        foreach ($mapping_fields_card_item as $new_field => $mapping_field){
          if($paragraph->hasField($new_field)){
            if(is_array($mapping_field)){
              foreach ($mapping_field as $property => $mapping_property){
                if($teaser->hasField($mapping_property) && !$teaser->get($mapping_property)->isEmpty()){
                  $paragraph->get($new_field)->$property = $teaser->get($mapping_property)->value;
                  $updated = TRUE;
                }
              }
            }else if($teaser->hasField($mapping_field)){
              if(substr($new_field, 0, 6) === 'field_'){
                $paragraph->get($new_field)->setValue($teaser->get($mapping_field)->getValue());
                $updated = TRUE;
              }else{
                $paragraph->$new_field = $teaser->get($mapping_field)->value;
                $updated = TRUE;
              }
            }
          }
        }
      }
    }

    if($updated){
      $paragraph->save();
    }
  }

  //Removes old fields in ln_c_cardgrid.
  foreach (['field_ln_c_card_title', 'field_c_cardgrid_title_style', 'field_ln_c_card_carousels', 'field_ln_c_grid_card_style', 'field_ln_c_card_allow_spacing', 'field_ln_card_color', 'field_number_of_columns'] as $field_name) {
    if($field = FieldConfig::loadByName('paragraph', 'ln_c_cardgrid', $field_name)) {
      $field->delete();
    }
  }

  //Removes old fields in ln_c_grid_card_item.
  foreach (['field_ln_c_grid_card_color', 'field_ln_c_grid_card_teaser'] as $field_name) {
    if($field = FieldConfig::loadByName('paragraph', 'ln_c_grid_card_item', $field_name)) {
      $field->delete();
    }
  }

  //Removes old classys
  foreach (['ln_c_gridcard_basic_style_1', 'ln_c_gridcard_basic_style_2', 'ln_c_gridcard_basic_style_3', 'ln_c_gridcard_hover_box_1', 'ln_c_gridcard_hover_box_2', 'ln_c_gridcard_quick_info_box', 'ln_c_gridcard_rollover'] as $classy_name) {
    if($classy = ClassyParagraphsStyle::load($classy_name)) {
      $classy->delete();
    }
  }
}

<?php

/**
 * @file
 * Post update functions for dsu_c_accordion module.
 */

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\field\Entity\FieldConfig;

/**
 * Migrate old field values to news
 */
function dsu_c_accordion_post_update_refactor_fields_migrate(&$sandbox = NULL) {
  $mapping_fields = [
    'field_c_subitems' => 'field_accordion_item',
  ];
  foreach(Paragraph::loadMultiple() as $paragraph){
    if($paragraph->bundle() == 'accordion'){
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

  //Removes old fields for Accordion.
  if($field = FieldConfig::loadByName('paragraph', 'accordion', 'field_accordion_item')) {
    $field->delete();
  }


}

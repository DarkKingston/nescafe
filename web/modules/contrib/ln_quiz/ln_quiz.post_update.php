<?php

/**
 * @file
 * Post update functions for ln_quiz module.
 */

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Migrate old field values to news
 */
function ln_quiz_post_update_refactor_fields_migrate(&$sandbox = NULL) {
  foreach(Paragraph::loadMultiple() as $paragraph){
    if($paragraph->bundle() == 'quiz_question'){
      if($paragraph->hasField('field_quiz_answer') && !$paragraph->get('field_quiz_answer')->isEmpty()){
        $paragraph->get('field_quiz_answers')->setValue([
          [
            'value' => t('True'),
            'right' => $paragraph->get('field_quiz_answer')->value
          ],[
            'value' => t('False'),
            'right' => !$paragraph->get('field_quiz_answer')->value
          ]
        ]);
        $paragraph->save();
      }
    }
  }

  //Removes old field.
  if($field = FieldConfig::loadByName('paragraph', 'quiz_question', 'field_quiz_answer')) {
    $field->delete();
  }
}

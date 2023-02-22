<?php

use Drupal\node\NodeInterface;
use Drupal\ln_srh\SRHConstants;
use Drupal\ln_srh_basic\SRHBasicConstants;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\Core\Url;

/**
 * Implements hook_token_info().
 */
function ln_srh_basic_token_info(){
  $info['tokens']['node']['srh_ingredients'] = [
    'name' => t('SRH Ingredients list'),
    'description' => t('SRH Ingredients full name list'),
  ];
  $info['tokens']['node']['srh_steps'] = [
    'name' => t('SRH Steps list'),
    'description' => t('SRH Steps list'),
  ];
  $info['tokens']['node']['srh_images'] = [
    'name' => t('SRH Images url list'),
    'description' => t('SRH Images url list'),
  ];
  $info['tokens']['node']['srh_videos'] = [
    'name' => t('SRH Videos url list'),
    'description' => t('SRH Videos url list'),
  ];

  return $info;
}

/**
 * Implements hook_tokens().
 */
function ln_srh_basic_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];
  if ($type == 'node' && isset($data['node']) && !empty($data['node'])) {
    /** @var NodeInterface $node */
    $node = $data['node'];
    if ($node->getType() == SRHConstants::SRH_RECIPE_BUNDLE) {
      foreach ($tokens as $name => $original) {
        switch ($name) {
          case 'srh_ingredients':
            $replacements[$original] = _ln_srh_basic_ingredients_list($node);
            break;
          case 'srh_steps':
            $replacements[$original] = _ln_srh_basic_steps_list_serialize($node);
            break;
          case 'srh_images':
            $replacements[$original] = _ln_srh_basic_images_list($node);
            break;
          case 'srh_videos':
            $replacements[$original] = _ln_srh_basic_videos_list($node);
            break;
        }
      }
    }
  }

  return $replacements;
}

function _ln_srh_basic_ingredients_list(NodeInterface $recipe){
  $fullNames = [];
  if($recipe->hasField(SRHBasicConstants::SRH_RECIPE_INGREDIENTS_FIELD) && !$recipe->get(SRHBasicConstants::SRH_RECIPE_INGREDIENTS_FIELD)->isEmpty()){
    $ingredients = $recipe->get(SRHBasicConstants::SRH_RECIPE_INGREDIENTS_FIELD)->referencedEntities();
    /** @var ParagraphInterface $ingredient */
    foreach ($ingredients as $ingredient){
      if($ingredient->hasField('field_c_title') && !$ingredient->get('field_c_title')->isEmpty()){
        $fullNames[] = str_replace(',',' ',$ingredient->get('field_c_title')->getString());
      }
    }
  }
  return implode(',',$fullNames);
}

function _ln_srh_basic_steps_list(NodeInterface $recipe){
  $fullNames = [];
  if($recipe->hasField(SRHBasicConstants::SRH_RECIPE_STEPS_FIELD) && !$recipe->get(SRHBasicConstants::SRH_RECIPE_STEPS_FIELD)->isEmpty()){
    $steps = $recipe->get(SRHBasicConstants::SRH_RECIPE_STEPS_FIELD)->referencedEntities();
    /** @var ParagraphInterface $step */
    foreach ($steps as $step){
      if($step->hasField('field_c_text') && !$step->get('field_c_text')->isEmpty()){
        $fullNames[] = str_replace(',',' ', $step->get('field_c_text')->getString());
      }
    }
  }
  return implode(',',$fullNames);
}

function _ln_srh_basic_steps_list_serialize(NodeInterface $recipe){
  $steps_list = [];
  if($recipe->hasField(SRHBasicConstants::SRH_RECIPE_STEPS_FIELD) && !$recipe->get(SRHBasicConstants::SRH_RECIPE_STEPS_FIELD)->isEmpty()){
    $steps = $recipe->get(SRHBasicConstants::SRH_RECIPE_STEPS_FIELD)->referencedEntities();
    /** @var ParagraphInterface $step */
    foreach ($steps as $step){
      $text = '';
      $name = '';
      $image = '';
      if($step->hasField('field_c_text') && !$step->get('field_c_text')->isEmpty()){
        $text = str_replace(',',' ', $step->get('field_c_text')->getString());
      }
      if($step->hasField('field_c_title') && !$step->get('field_c_title')->isEmpty()){
        $name = $step->get('field_c_title')->getString();
      }
      if($step->hasField('field_srh_media') && !$step->get('field_srh_media')->isEmpty()){
        /** @var \Drupal\media\MediaInterface $media */
        $media = $step->get('field_srh_media')->entity;
        if($media->bundle() == 'image'){
          if($media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()){
            /** @var \Drupal\file\FileInterface $file */
            $file = $media->get('field_media_image')->entity;
            $image = [
              '@type' => 'ImageObject',
              'url' => Url::fromUri(file_create_url($file->getFileUri()))->toString(),
            ];
          }
        }
      }
      $steps_list[] = [
        '@type' => 'HowToStep',
        'name' => $name,
        'text' => $text,
        'url' => '',
        'image' => $image,
      ];
    }
  }

  return serialize($steps_list);
}

function _ln_srh_basic_images_list(NodeInterface $recipe){
  $urls = [];
  if($recipe->hasField(SRHBasicConstants::SRH_RECIPE_GALLERY_FIELD) && !$recipe->get(SRHBasicConstants::SRH_RECIPE_GALLERY_FIELD)->isEmpty()){
    $medias = $recipe->get(SRHBasicConstants::SRH_RECIPE_GALLERY_FIELD)->referencedEntities();
    /** @var \Drupal\media\MediaInterface $media */
    foreach ($medias as $media){
      if($media->bundle() == 'image'){
        if($media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()){
          /** @var \Drupal\file\FileInterface $file */
          $file = $media->get('field_media_image')->entity;
          $urls[] = Url::fromUri(file_create_url($file->getFileUri()))->toString();
        }
      }
    }
  }
  return implode(',',$urls);
}
function _ln_srh_basic_videos_list(NodeInterface $recipe){
  $urls = [];
  if($recipe->hasField(SRHBasicConstants::SRH_RECIPE_GALLERY_FIELD) && !$recipe->get(SRHBasicConstants::SRH_RECIPE_GALLERY_FIELD)->isEmpty()){
    $medias = $recipe->get(SRHBasicConstants::SRH_RECIPE_GALLERY_FIELD)->referencedEntities();
    /** @var \Drupal\media\MediaInterface $media */
    foreach ($medias as $media){
      if($media->bundle() == 'remote_video'){
        if($media->hasField('field_media_oembed_video') && !$media->get('field_media_oembed_video')->isEmpty()){
          $urls[] = $media->get('field_media_oembed_video')->getString();
        }
      }
    }
  }
  return implode(',',$urls);
}

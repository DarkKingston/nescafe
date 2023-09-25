<?php

use Drupal\node\NodeInterface;
use Drupal\ln_srh\SRHConstants;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\ln_srh_full\SRHFullConstants;

/**
 * Implements hook_token_info().
 */
function ln_srh_full_token_info() {
  $info['tokens']['node']['srh_cuisine'] = [
    'name' => t('SRH Recipe Cuisine'),
    'description' => t('SRH Recipe Cuisine'),
  ];
  $info['tokens']['node']['srh_course'] = [
    'name' => t('SRH Recipe Course'),
    'description' => t('SRH Recipe Course'),
  ];
  $info['tokens']['node']['srh_keywords'] = [
    'name' => t('SRH Recipe Keywords'),
    'description' => t('SRH Recipe Keywords'),
  ];

  return $info;
}

/**
 * Implements hook_tokens().
 */
function ln_srh_full_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];
  if ($type == 'node' && isset($data['node']) && !empty($data['node'])) {
    /** @var NodeInterface $node */
    $node = $data['node'];
    if ($node->getType() == SRHConstants::SRH_RECIPE_BUNDLE) {
      foreach ($tokens as $name => $original) {
        switch ($name) {
          case 'srh_cuisine':
            $replacements[$original] = _ln_srh_full_tag($node,SRHFullConstants::SRH_CUISINE_TAG_ID);
            break;
          case 'srh_course':
            $replacements[$original] = _ln_srh_full_tag($node,SRHFullConstants::SRH_COURSE_TAG_ID);
            break;
          case 'srh_keywords':
            $replacements[$original] = _ln_srh_full_keywords($node);
            break;
        }
      }
    }
  }

  return $replacements;
}

function _ln_srh_full_tag(NodeInterface $recipe, $tag_type) {
  $tags = [];
  if ($recipe->hasField(SRHFullConstants::SRH_RECIPE_TAGGING_FIELD) && !$recipe->get(SRHFullConstants::SRH_RECIPE_TAGGING_FIELD)->isEmpty()) {
    $tagging = $recipe->get(SRHFullConstants::SRH_RECIPE_TAGGING_FIELD)->referencedEntities();
    /** @var \Drupal\taxonomy\TermInterface $tag */
    foreach ($tagging as $tag) {
      $parents = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($tag->id());
      /** @var \Drupal\taxonomy\TermInterface $parent */
      $parent = reset($parents);
      if ($parent && $parent->get(SRHConstants::SRH_RECIPE_EXTERNAL_FIELD)->getString() == $tag_type) {
        $tags[$tag->id()] = $tag->label();
      }
    }
  }
  return implode(',', $tags);
}

function _ln_srh_full_keywords(NodeInterface $recipe) {
  $keywords = [];
  if ($recipe->hasField(SRHFullConstants::SRH_RECIPE_TAGGING_FIELD) && !$recipe->get(SRHFullConstants::SRH_RECIPE_TAGGING_FIELD)->isEmpty()) {
    $tagging = $recipe->get(SRHFullConstants::SRH_RECIPE_TAGGING_FIELD)->referencedEntities();
    /** @var \Drupal\taxonomy\TermInterface $tag */
    foreach ($tagging as $tag) {
      $keywords[] = $tag->getName();
    }
  }
  return implode(',', $keywords);
}

<?php

namespace Drupal\ln_srh_menuiq\Plugin\DsField\Paragraph;

use Drupal\ds\Plugin\DsField\DsFieldBase;
use Drupal\ln_srh_menuiq\SRHMyMenuIQConstants;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Plugin that renders a recipe difficulty label
 *
 * @DsField(
 *   id = "srh_sidedish_difficulty",
 *   title = @Translation("SRH Sidedish Difficulty"),
 *   provider = "ln_srh_menuiq",
 *   entity_type = "paragraph",
 *   ui_limit = {"srh_sidedish|*"},
 * )
 */

class SRHSidedishDifficulty extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $sideDish = $this->entity();
    if($recipe = $sideDish->get(SRHMyMenuIQConstants::SRH_SIDEDISH_RECIPE_FIELD)->entity){
      if($difficulty = $this->getRecipeDifficulty($recipe)){
        return[
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#attributes' => [
            'class' => ['srh-sidedish-difficulty'],
          ],
          '#value' => $difficulty,
        ];
      }
    }
    return [];
  }

  public function getRecipeDifficulty(NodeInterface $recipe){
    if($recipe->hasField(SRHMyMenuIQConstants::SRH_RECIPE_DIFFICULTY_FIELD) && !$recipe->get(SRHMyMenuIQConstants::SRH_RECIPE_DIFFICULTY_FIELD)->isEmpty()){
      /** @var Term $difficulty */
      $difficulty = $recipe->get(SRHMyMenuIQConstants::SRH_RECIPE_DIFFICULTY_FIELD)->entity;
      return $difficulty->label();
    }

    return FALSE;
  }
}

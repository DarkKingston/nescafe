<?php

namespace Drupal\ln_srh_menuiq\Plugin\DsField\Paragraph;

use Drupal\ds\Plugin\DsField\DsFieldBase;
use Drupal\ln_srh_menuiq\SRHMyMenuIQConstants;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Plugin that renders a recipe duration time
 *
 * @DsField(
 *   id = "srh_sidedish_duration",
 *   title = @Translation("SRH Sidedish Duration"),
 *   provider = "ln_srh_menuiq",
 *   entity_type = "paragraph",
 *   ui_limit = {"srh_sidedish|*"},
 * )
 */

class SRHSidedishDuration extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $sideDish = $this->entity();
    if($recipe = $sideDish->get(SRHMyMenuIQConstants::SRH_SIDEDISH_RECIPE_FIELD)->entity){
      if($duration = $this->getRecipeDuration($recipe)){
        return [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#attributes' => [
            'class' => ['srh-sidedish-duration']
          ],
          '#value' => $this->t('@duration min',['@duration' => $duration]),
        ];
      }
    }
    return [];
  }

  public function getRecipeDuration(NodeInterface $recipe){
    $duration = 0;
    if($recipe->hasField(SRHMyMenuIQConstants::SRH_RECIPE_STEPS_FIELD) && !$recipe->get(SRHMyMenuIQConstants::SRH_RECIPE_STEPS_FIELD)->isEmpty()){
      $steps = $recipe->get(SRHMyMenuIQConstants::SRH_RECIPE_STEPS_FIELD)->referencedEntities();
      /** @var ParagraphInterface $step */
      foreach ($steps as $step){
        if($step->hasField(SRHMyMenuIQConstants::SRH_STEP_DURATION_FIELD) && !$step->get(SRHMyMenuIQConstants::SRH_STEP_DURATION_FIELD)->isEmpty()){
          $duration += $step->get(SRHMyMenuIQConstants::SRH_STEP_DURATION_FIELD)->getString();
        }
      }

      return $duration;
    }

    return FALSE;
  }
}

<?php

namespace Drupal\ln_srh_extended\Plugin\DsField\Node;

use Drupal\ds\Plugin\DsField\DsFieldBase;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Plugin that renders total steps duration
 *
 * @DsField(
 *   id = "srh_steps_duration",
 *   title = @Translation("SRH Steps Duration"),
 *   provider = "ln_srh",
 *   entity_type = "node",
 *   ui_limit = {"srh_recipe|*"},
 * )
 */

class SRHStepsDuration extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var NodeInterface $recipe */
    $recipe = $this->entity();
    $duration = 0;
    if($recipe->hasField('field_srh_steps') && !$recipe->get('field_srh_steps')->isEmpty()){
      $steps = $recipe->get('field_srh_steps')->referencedEntities();
      /** @var ParagraphInterface $step */
      foreach ($steps as $step){
        if($step->hasField('field_srh_duration') && !$step->get('field_srh_duration')->isEmpty()){
          $duration += $step->get('field_srh_duration')->getString();
        }
      }
    }
    return [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => ['srh-steps-duration']
      ],
      '#value' => $this->t('@duration min',['@duration' => $duration]),
    ];
  }

}

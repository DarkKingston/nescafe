<?php

namespace Drupal\ln_srh\Plugin\Block;

use Drupal;
use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Recommended Recipes' block to be placed within the recipe
 * content.
 *
 * @Block(
 *   id = "recommended_recipes",
 *   admin_label = @Translation("Recommended Recipes"),
 *   category = @Translation("Recommended Recipes")
 * )
 */
class RecommendedRecipesBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#value' => $this->t('This block is deprecated and will be removed in later versions.'),
    ];
  }

}

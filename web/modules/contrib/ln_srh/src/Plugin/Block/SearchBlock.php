<?php

namespace Drupal\ln_srh\Plugin\Block;

use Drupal;
use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'search recipe' block.
 *
 * @Block(
 *   id = "search_recipes",
 *   admin_label = @Translation("Recipes block"),
 *   category = @Translation("Recipes block ")
 * )
 */
class SearchBlock extends BlockBase {

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

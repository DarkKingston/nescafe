<?php

namespace Drupal\dsu_ratings_reviews\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\user\Plugin\Field\FieldFormatter\UserNameFormatter;

/**
 * Plugin implementation of the 'user_name' formatter.
 *
 * @FieldFormatter(
 *   id = "user_name_no_cache",
 *   label = @Translation("User name without cache"),
 *   description = @Translation("Display the user or author name without cache."),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class UserNameFormatterNoCache extends UserNameFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = parent::viewElements($items, $langcode);
    }

    $elements['#cache']['max-age'] = 0;

    return $elements;
  }

}

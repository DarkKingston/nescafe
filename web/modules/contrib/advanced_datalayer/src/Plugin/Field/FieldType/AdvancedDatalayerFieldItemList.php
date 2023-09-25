<?php

namespace Drupal\advanced_datalayer\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Represents a list of datalayer field item objects.
 */
class AdvancedDatalayerFieldItemList extends FieldItemList {

  /**
   * {@inheritdoc}
   */
  public function hasAffectingChanges(FieldItemListInterface $original_items, $langcode) {
    $normalized_items = clone $this;
    $normalized_original_items = clone $original_items;

    // Remove default datalayer tags.
    $normalized_items->preSave();
    $normalized_items->filterEmptyItems();
    $normalized_original_items->preSave();
    $normalized_original_items->filterEmptyItems();

    return !$normalized_items->equals($normalized_original_items);
  }

}

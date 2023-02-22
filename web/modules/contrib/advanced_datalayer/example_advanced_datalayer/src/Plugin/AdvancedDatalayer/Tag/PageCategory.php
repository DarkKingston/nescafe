<?php

namespace Drupal\example_advanced_datalayer\Plugin\AdvancedDatalayer\Tag;

use Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Tag\DatalayerNameBase;

/**
 * The basic "pageCategory" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "page_Category",
 *   label = @Translation("Page category"),
 *   description = @Translation("Page category"),
 *   group = "page_Information",
 *   global = FALSE,
 *   required = FALSE,
 *   translatable = TRUE,
 *   show_empty = FALSE,
 *   weight = 2,
 * )
 */
class PageCategory extends DatalayerNameBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}

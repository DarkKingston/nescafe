<?php

namespace Drupal\example_advanced_datalayer\Plugin\AdvancedDatalayer\Tag;

use Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Tag\DatalayerNameBase;

/**
 * The basic "pageName" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "page_Name",
 *   label = @Translation("Page Name"),
 *   description = @Translation("Page Name"),
 *   group = "page_Information",
 *   global = FALSE,
 *   required = TRUE,
 *   translatable = TRUE,
 *   show_empty = FALSE,
 *   weight = 1,
 * )
 */
class PageName extends DatalayerNameBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}

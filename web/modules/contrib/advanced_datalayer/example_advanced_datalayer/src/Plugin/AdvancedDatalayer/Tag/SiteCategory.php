<?php

namespace Drupal\example_advanced_datalayer\Plugin\AdvancedDatalayer\Tag;

use Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Tag\DatalayerNameBase;

/**
 * The basic "siteCategory" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "site_Category",
 *   label = @Translation("Site Category"),
 *   description = @Translation("Site Category"),
 *   group = "site_Information",
 *   global = TRUE,
 *   required = FALSE,
 *   translatable = FALSE,
 *   show_empty = TRUE,
 *   weight = 2,
 * )
 */
class SiteCategory extends DatalayerNameBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}

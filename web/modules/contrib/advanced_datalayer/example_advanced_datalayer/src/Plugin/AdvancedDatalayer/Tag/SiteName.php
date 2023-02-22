<?php

namespace Drupal\example_advanced_datalayer\Plugin\AdvancedDatalayer\Tag;

use Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Tag\DatalayerNameBase;

/**
 * The basic "SiteName" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "site_Name",
 *   label = @Translation("Site name"),
 *   description = @Translation("Site name"),
 *   group = "site_Information",
 *   global = TRUE,
 *   required = TRUE,
 *   translatable = FALSE,
 *   show_empty = FALSE,
 *   weight = 10,
 * )
 */
class SiteName extends DatalayerNameBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}

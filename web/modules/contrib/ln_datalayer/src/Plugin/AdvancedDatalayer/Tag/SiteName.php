<?php

namespace Drupal\ln_datalayer\Plugin\AdvancedDatalayer\Tag;

use Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Tag\DatalayerNameBase;

/**
 * The basic "SiteName" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "siteName",
 *   label = @Translation("Site name"),
 *   description = @Translation("This parameter should contain the official Nestlé 'Site Name'; typically 'BRAND' + 'COUNTRY'."),
 *   group = "siteInformation",
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

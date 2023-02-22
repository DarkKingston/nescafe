<?php

namespace Drupal\ln_datalayer\Plugin\AdvancedDatalayer\Tag;

use Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Tag\DatalayerNameBase;

/**
 * The basic "siteCategory" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "siteCategory",
 *   label = @Translation("Site Category"),
 *   description = @Translation("This parameter should contain the Nestle Category for the site (may be the same as ‘business’ or different, depending on the site/page) (ex Petcare, Confectionary, Beverages)"),
 *   group = "siteInformation",
 *   global = TRUE,
 *   required = TRUE,
 *   translatable = FALSE,
 *   show_empty = FALSE,
 *   weight = 17,
 * )
 */
class SiteCategory extends DatalayerNameBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}

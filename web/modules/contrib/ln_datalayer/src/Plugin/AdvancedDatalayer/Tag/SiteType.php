<?php

namespace Drupal\ln_datalayer\Plugin\AdvancedDatalayer\Tag;

/**
 * The basic "siteType" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "siteType",
 *   label = @Translation("Site type"),
 *   description = @Translation("This parameter should contain the designation for the  type of digital asset, typically 'Site' or 'Application'."),
 *   group = "siteInformation",
 *   global = TRUE,
 *   required = TRUE,
 *   translatable = FALSE,
 *   show_empty = FALSE,
 *   weight = 11,
 * )
 */
class SiteType extends DatalayerDatasetsBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}

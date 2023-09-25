<?php

namespace Drupal\ln_datalayer\Plugin\AdvancedDatalayer\Tag;

/**
 * The basic "SubBrand" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "subBrand",
 *   label = @Translation("Sub brand"),
 *   description = @Translation("This parameter should contain the 'sub-brand' featured on the site, based on Nestlé's specifications"),
 *   group = "siteInformation",
 *   global = TRUE,
 *   required = TRUE,
 *   translatable = FALSE,
 *   show_empty = FALSE,
 *   weight = 5,
 * )
 */
class SubBrand extends DatalayerDatasetsBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}

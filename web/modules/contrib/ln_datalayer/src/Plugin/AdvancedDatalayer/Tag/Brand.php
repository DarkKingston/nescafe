<?php

namespace Drupal\ln_datalayer\Plugin\AdvancedDatalayer\Tag;

/**
 * The basic "brand" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "brand",
 *   label = @Translation("Brand"),
 *   description = @Translation("This parameter should contain the 'Brand' featured on the site, based on Nestlé's specifications"),
 *   group = "siteInformation",
 *   global = TRUE,
 *   required = TRUE,
 *   translatable = FALSE,
 *   show_empty = FALSE,
 *   weight = 4,
 * )
 */
class Brand extends DatalayerDatasetsBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}

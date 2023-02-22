<?php

namespace Drupal\ln_datalayer\Plugin\AdvancedDatalayer\Tag;

/**
 * The basic "country" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "country",
 *   label = @Translation("Country"),
 *   description = @Translation("This  parameter  should  contain  the  'Country'  of  the  site,  based  on Nestlé's specifications"),
 *   group = "siteInformation",
 *   global = TRUE,
 *   required = TRUE,
 *   translatable = FALSE,
 *   show_empty = FALSE,
 *   weight = 2,
 * )
 */
class Country extends DatalayerDatasetsBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}

<?php

namespace Drupal\ln_datalayer\Plugin\AdvancedDatalayer\Tag;

/**
 * The basic "business" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "business",
 *   label = @Translation("Business"),
 *   description = @Translation("This  parameter  should  contain  the  'Business'  for  the  site,  based  on Nestlé's specifications"),
 *   group = "siteInformation",
 *   global = TRUE,
 *   required = TRUE,
 *   translatable = FALSE,
 *   show_empty = FALSE,
 *   weight = 3,
 * )
 */
class Business extends DatalayerDatasetsBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}

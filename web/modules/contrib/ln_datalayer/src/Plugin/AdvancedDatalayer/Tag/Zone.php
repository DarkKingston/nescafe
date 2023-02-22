<?php

namespace Drupal\ln_datalayer\Plugin\AdvancedDatalayer\Tag;

/**
 * The basic "zone" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "zone",
 *   label = @Translation("Zone"),
 *   description = @Translation("This  parameter  should  contain  the 'Zone'  for  the  site,  based  on Nestlé's specifications (ex. Global ; AOA ; AMS ; EUR)."),
 *   group = "siteInformation",
 *   global = TRUE,
 *   required = TRUE,
 *   translatable = FALSE,
 *   show_empty = FALSE,
 *   weight = 1,
 * )
 */
class Zone extends DatalayerDatasetsBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}

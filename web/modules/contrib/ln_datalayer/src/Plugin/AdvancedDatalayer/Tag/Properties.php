<?php

namespace Drupal\ln_datalayer\Plugin\AdvancedDatalayer\Tag;

/**
 * The basic "properties" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "properties",
 *   label = @Translation("Properties"),
 *   description = @Translation("This parameter should contain the general  property(s) for the site.  If there are multiple, they should be separated by a pipe '|' symbol."),
 *   group = "siteInformation",
 *   global = TRUE,
 *   required = TRUE,
 *   translatable = FALSE,
 *   show_empty = FALSE,
 *   weight = 9,
 * )
 */
class Properties extends DatalayerDatasetsBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}

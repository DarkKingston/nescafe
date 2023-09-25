<?php

namespace Drupal\ln_datalayer\Plugin\AdvancedDatalayer\Tag;

/**
 * The basic "language" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "language",
 *   label = @Translation("Language"),
 *   description = @Translation("This parameter should contain the language that the site is written in."),
 *   group = "siteInformation",
 *   global = TRUE,
 *   required = TRUE,
 *   translatable = FALSE,
 *   show_empty = FALSE,
 *   weight = 7,
 * )
 */
class Language extends DatalayerDatasetsBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}

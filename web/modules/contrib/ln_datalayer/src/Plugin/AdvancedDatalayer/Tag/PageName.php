<?php

namespace Drupal\ln_datalayer\Plugin\AdvancedDatalayer\Tag;

use Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Tag\DatalayerNameBase;

/**
 * The basic "pageName" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "pageName",
 *   label = @Translation("Page Name"),
 *   description = @Translation("This parameter should contain the name assigned to a page -typically the page title (ex. Purina: Nutritious Dog and Cat Food, Nespresso | Coffee & Espresso Machine, etc.)."),
 *   group = "pageInformation",
 *   global = FALSE,
 *   required = FALSE,
 *   translatable = TRUE,
 *   show_empty = FALSE,
 *   weight = 2,
 * )
 */
class PageName extends DatalayerNameBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}

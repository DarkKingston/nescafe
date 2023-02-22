<?php

namespace Drupal\ln_datalayer\Plugin\AdvancedDatalayer\Tag;

use Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Tag\DatalayerNameBase;

/**
 * The basic "SiteName" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "pageSubsection",
 *   label = @Translation("Page Subsection"),
 *   description = @Translation("This  parameter  should  contain  the  sub  section  assigned  to  a  page.  Actual values can vary from website to website (ex. Food, Beverages, Health, Fitness, etc.)."),
 *   group = "pageInformation",
 *   global = FALSE,
 *   required = FALSE,
 *   translatable = FALSE,
 *   show_empty = FALSE,
 *   weight = 5,
 * )
 */
class PageSubsection extends DatalayerNameBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}

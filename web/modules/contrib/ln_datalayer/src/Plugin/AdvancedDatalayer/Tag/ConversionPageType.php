<?php

namespace Drupal\ln_datalayer\Plugin\AdvancedDatalayer\Tag;

use Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Tag\DatalayerNameBase;

/**
 * The basic "conversionPageType" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "conversionPageType",
 *   label = @Translation("Conversion page type"),
 *   description = @Translation("This  parameter  should  contain  the  type  of  page  where  the  user  is completing a transaction or conversion.  Actual values can vary from website to website"),
 *   group = "siteInformation",
 *   global = FALSE,
 *   required = FALSE,
 *   translatable = FALSE,
 *   show_empty = FALSE,
 *   weight = 8,
 * )
 */
class ConversionPageType extends DatalayerNameBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}

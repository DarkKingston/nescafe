<?php

namespace Drupal\ln_datalayer\Plugin\AdvancedDatalayer\Tag;

use Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Tag\DatalayerNameBase;

/**
 * The basic "businessUnit" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "businessUnit",
 *   label = @Translation("Business unit"),
 *   description = @Translation("This parameter will only be used by specific business units, and those business units will have a standardized value that gets placed here.  Examples  of  these  units  include  Nestlé  Health  Science  or  Nestlé Infant Nutrition."),
 *   group = "siteInformation",
 *   global = TRUE,
 *   required = TRUE,
 *   translatable = FALSE,
 *   show_empty = FALSE,
 *   weight = 6,
 * )
 */
class BusinessUnit extends DatalayerNameBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}

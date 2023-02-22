<?php

namespace Drupal\example_advanced_datalayer\Plugin\AdvancedDatalayer\Group;

use Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Group\GroupBase;

/**
 * The basic group.
 *
 * @AdvancedDatalayerGroup(
 *   id = "site_Information",
 *   label = @Translation("Site Information Group"),
 *   description = @Translation("Site Information datalayer tags group."),
 *   weight = 2
 * )
 */
class SiteInformation extends GroupBase {
  // Inherits everything from Base.
}

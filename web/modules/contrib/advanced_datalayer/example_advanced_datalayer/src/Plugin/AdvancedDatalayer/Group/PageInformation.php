<?php

namespace Drupal\example_advanced_datalayer\Plugin\AdvancedDatalayer\Group;

use Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Group\GroupBase;

/**
 * The advanced group.
 *
 * @AdvancedDatalayerGroup(
 *   id = "page_Information",
 *   label = @Translation("Page Information Group"),
 *   description = @Translation("Page Information datalayer tags group."),
 *   weight = 1
 * )
 */
class PageInformation extends GroupBase {
  // Inherits everything from Base.
}

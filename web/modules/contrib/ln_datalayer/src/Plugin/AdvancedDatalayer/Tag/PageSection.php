<?php

namespace Drupal\ln_datalayer\Plugin\AdvancedDatalayer\Tag;

use Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Tag\DatalayerNameBase;

/**
 * The basic "PageSection" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "pageSection",
 *   label = @Translation("Page Section"),
 *   description = @Translation("This parameter should contain the section assigned to a page. Actual values can vary from website to website (ex Articles, Products, Blog, etc.)."),
 *   group = "pageInformation",
 *   global = FALSE,
 *   required = FALSE,
 *   translatable = FALSE,
 *   show_empty = FALSE,
 *   weight = 4,
 * )
 */
class PageSection extends DatalayerNameBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}

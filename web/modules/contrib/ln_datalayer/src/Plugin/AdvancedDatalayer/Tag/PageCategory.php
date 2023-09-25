<?php

namespace Drupal\ln_datalayer\Plugin\AdvancedDatalayer\Tag;

use Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Tag\DatalayerNameBase;

/**
 * The basic "pageCategory" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "pageCategory",
 *   label = @Translation("Page category"),
 *   description = @Translation("This  parameter  should  contain  the  category  assigned  to  a  page.  Actual values can vary from website to website (ex. Product Page, Checkout, Home, )."),
 *   group = "pageInformation",
 *   global = FALSE,
 *   required = FALSE,
 *   translatable = FALSE,
 *   show_empty = FALSE,
 *   weight = 1,
 * )
 */
class PageCategory extends DatalayerNameBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}

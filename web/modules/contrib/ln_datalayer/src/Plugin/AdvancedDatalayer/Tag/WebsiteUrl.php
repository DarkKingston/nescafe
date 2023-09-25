<?php

namespace Drupal\ln_datalayer\Plugin\AdvancedDatalayer\Tag;

use Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Tag\DatalayerNameBase;

/**
 * The basic "websiteUrl" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "websiteUrl",
 *   label = @Translation("Website Url"),
 *   description = @Translation("This  parameter  should  contain  the  complete  URL  (including  the directory  path)  for  the  website,  as  specified  in  the  Nestlé  DigiPi database"),
 *   group = "siteInformation",
 *   global = TRUE,
 *   required = TRUE,
 *   translatable = TRUE,
 *   show_empty = FALSE,
 *   weight = 13,
 * )
 */
class WebsiteUrl extends DatalayerNameBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}

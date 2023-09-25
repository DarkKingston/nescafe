<?php

namespace Drupal\ln_datalayer\Plugin\AdvancedDatalayer\Tag;

use Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Tag\DatalayerNameBase;

/**
 * The basic "digiPiID" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "digiPiID",
 *   label = @Translation("digiPi ID"),
 *   description = @Translation("This  parameter  should  contain  the  Nestlé  DigiPi  ID  assigned  to  the website,  application  or  digital  asset.  The  value  can  be  obtained  in: http://webapps6.Nestlé.com/DIGIPI/UI/Search.aspx (Ask the LGO)"),
 *   group = "siteInformation",
 *   global = TRUE,
 *   required = TRUE,
 *   translatable = FALSE,
 *   show_empty = FALSE,
 *   weight = 12,
 * )
 */
class DigiPiId extends DatalayerNameBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}

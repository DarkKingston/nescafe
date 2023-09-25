<?php

namespace Drupal\ln_datalayer\Plugin\AdvancedDatalayer\Tag;

use Drupal\advanced_datalayer\Plugin\AdvancedDatalayer\Tag\DatalayerDynamicNameBase;

/**
 * The basic "deviceType" datalayer tag.
 *
 * @AdvancedDatalayerTag(
 *   id = "deviceType",
 *   label = @Translation("Device type"),
 *   description = @Translation("This  parameter  should  contain  the  category  of  device (desktop, mobile, or tablet) used by the user to access your site/app. (ex. Desktop, Mobile, Tablet)"),
 *   group = "userInformation",
 *   global = TRUE,
 *   required = FALSE,
 *   translatable = FALSE,
 *   show_empty = TRUE,
 *   weight = 1,
 * )
 */
class DeviceType extends DatalayerDynamicNameBase {
  // Nothing here yet. Just a placeholder class for a plugin
  // device type will be calculated on JS side.
}

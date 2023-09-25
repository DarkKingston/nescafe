<?php

namespace Drupal\ln_srh_basic\Plugin\metatag\Tag;

use Drupal\metatag\Plugin\metatag\Tag\MetaPropertyBase;

/**
 * The Instagram "instagram:description" meta tag.
 *
 * @MetatagTag(
 *   id = "srh_ins_description",
 *   label = @Translation("Instagram Description"),
 *   description = @Translation("The Instagram description meta tag."),
 *   name = "og:description",
 *   group = "ln_srh_instagram",
 *   weight = 6,
 *   type = "label",
 *   secure = FALSE,
 *   multiple = FALSE,
 *   long = TRUE,
 * )
 */
class SRHInsDescription extends MetaPropertyBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}

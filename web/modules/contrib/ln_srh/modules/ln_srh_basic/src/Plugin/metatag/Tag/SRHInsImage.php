<?php

namespace Drupal\ln_srh_basic\Plugin\metatag\Tag;

use Drupal\metatag\Plugin\metatag\Tag\MetaPropertyBase;

/**
 * The Instagram "instagram:image" meta tag.
 *
 * @MetatagTag(
 *   id = "srh_ins_image",
 *   label = @Translation("Instagram Image"),
 *   description = @Translation("The Instagram image meta tag."),
 *   name = "og:image",
 *   group = "ln_srh_instagram",
 *   weight = 9,
 *   type = "image",
 *   secure = FALSE,
 *   multiple = TRUE,
 *   absolute_url = TRUE
 * )
 */
class SRHInsImage extends MetaPropertyBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}

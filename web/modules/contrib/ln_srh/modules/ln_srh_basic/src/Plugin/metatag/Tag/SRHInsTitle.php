<?php

namespace Drupal\ln_srh_basic\Plugin\metatag\Tag;

use Drupal\metatag\Plugin\metatag\Tag\MetaPropertyBase;

/**
 * The Instagram "instagram:title" meta tag.
 *
 * @MetatagTag(
 *   id = "srh_ins_title",
 *   label = @Translation("Instagram Title"),
 *   description = @Translation("The Instagram title meta tag."),
 *   name = "og:title",
 *   group = "ln_srh_instagram",
 *   weight = 4,
 *   type = "label",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class SRHInsTitle extends MetaPropertyBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}

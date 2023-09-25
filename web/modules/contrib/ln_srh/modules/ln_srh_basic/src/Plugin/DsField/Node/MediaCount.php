<?php

namespace Drupal\ln_srh_basic\Plugin\DsField\Node;

use Drupal\ln_srh_basic\Plugin\DsField\CountBase;
use Drupal\ln_srh_basic\SRHBasicConstants;

/**
 * Plugin that renders the media count
 *
 * @DsField(
 *   id = "srh_recipe_media_count",
 *   title = @Translation("Media count"),
 *   provider = "ln_srh_basic",
 *   entity_type = "node",
 *   ui_limit = {"srh_recipe|*"},
 * )
 */

class MediaCount extends CountBase {
  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {

    $configuration = [
      'singular_label' => 'photo',
      'plural_label' => 'photos',
    ];

    return $configuration;
  }


  /**
   * {@inheritdoc}
   */
  public function getFieldName() {
    return SRHBasicConstants::SRH_RECIPE_GALLERY_FIELD;
  }
}

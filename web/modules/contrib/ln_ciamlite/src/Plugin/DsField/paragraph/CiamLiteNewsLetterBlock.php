<?php

namespace Drupal\ln_ciamlite\Plugin\DsField\paragraph;

use Drupal\ds\Plugin\DsField\BlockBase;
use Drupal\ln_ciamlite\LnCiamliteConstants;

/**
 * Plugin that renders the 'Ciamlite Newsletter block'
 *
 * @DsField(
 *   id = "ln_ciamlite_newsleter",
 *   title = @Translation("Ciamlite Newsletter"),
 *   provider = "ln_ciamlite",
 *   entity_type = "paragraph",
 *   ui_limit = {"ln_ciamlite_newsletter_block|*"},
 * )
 */
class CiamLiteNewsLetterBlock extends BlockBase{

  public function blockPluginId(){
    return LnCiamliteConstants::LN_CIAMLITE_NEWLETTER_SCREEN_PLUGIN_ID;
  }

}

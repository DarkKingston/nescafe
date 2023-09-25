<?php

namespace Drupal\ln_ciamlite\Services;


interface CiamLiteHelperInterface{


  /**
   *
   * @param string $unique_id
   * @return array
   */
  public function buildGigyaNewsletterScreen($unique_id);

}

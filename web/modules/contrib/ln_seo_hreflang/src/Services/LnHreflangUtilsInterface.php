<?php

namespace Drupal\ln_seo_hreflang\Services;


/**
 * Interface LnHreflangUtilsInterface.
 */
interface LnHreflangUtilsInterface {

  /**
   * Returns the abc belonging to the current path if it exists
   *
   * @return \Drupal\ln_seo_hreflang\Entity\LnHreflangInterface[]
   */
  public function getCurrentHreflangs();
}

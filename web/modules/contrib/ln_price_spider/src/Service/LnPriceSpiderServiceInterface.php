<?php

namespace Drupal\ln_price_spider\Service;

use Drupal\Core\Entity\EntityInterface;

/**
 * Ligtnest Price Spider interface to extend or getting services.
 */
interface LnPriceSpiderServiceInterface {

  /**
   * Get entity locale Codes.
   */
  public function getEntityLocale(EntityInterface $entity);

  /**
   * Replace entity code with mapping required for price spider api.
   */
  public function getPriceSpiderCountryCode($locale);

  /**
   * Get current language code for entity.
   */
  public function getPriceSpiderLangCode();

}

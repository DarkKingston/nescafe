<?php

namespace Drupal\ln_pdh;

/**
 * Interface PdhConnectorInterface. Manage connection to pdh service.
 *
 * @package Drupal\ln_pdh
 */
interface PdhConnectorInterface {

  /**
   * Checks if PDH connection is working.
   *
   * @return bool
   *   TRUE if successful, FALSE otherwise.
   */
  public function testConnection();

  /**
   * Retrieves a list of products updated since a given date.
   *
   * @param \DateTime|null $since_date
   *   (Optional) The datetime for filtering updated products.
   *
   * @return array|false
   *   Array containing the returned products. FALSE if anything went wrong.
   */
  public function getProducts(\DateTime $since_date = NULL);

  /**
   * Retrieves the information about a given product.
   *
   * @param string $gtin
   *   The product GTIN.
   * @param int $label_version
   *   The product label version.
   * @param \DateTime|null $since_date
   *   (Optional) The datetime for filtering updated products.
   *
   * @return \SimpleXMLElement|false
   *   The product object if available. FALSE otherwise
   */
  public function getProductInfo($gtin, $label_version, \DateTime $since_date = NULL);

}

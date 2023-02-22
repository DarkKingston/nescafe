<?php

namespace Drupal\ln_bazaarvoice\Service;

use Drupal\Core\Entity\EntityInterface;

/**
 * Ligtnest Bazaarvoice interface to extend or getting services.
 */
interface LnBazaarvoiceServiceInterface {

  /**
   * Get Bazaarvoice JS path.
   *
   * @return string
   *   The js url.
   */
  public function getBazaarvoiceJsPath();

  /**
   * Get product info.
   *
   * @param $bazaarvoice_id
   *   The bazaarvoice id
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to which the bazaarvoice field belongs
   * @param array $dcc_info
   *   DCC mapping
   *
   * @return array
   *   The product info array.
   */
  public function getProductInfo($bazaarvoice_id, EntityInterface $entity, $dcc_info);
}

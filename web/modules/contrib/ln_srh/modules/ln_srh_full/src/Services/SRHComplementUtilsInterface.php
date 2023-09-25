<?php

namespace Drupal\ln_srh_full\Services;

use Drupal\node\NodeInterface;
use Drupal\ln_srh\SRHException;

interface SRHComplementUtilsInterface {

  /**
   * @return array
   */
  public function syncComplements();

  /**
   * @param mixed $srh_complement
   * @return mixed
   * @throws SRHException
   */
  public function syncComplement($srh_complement);


  /**
   * @param $srh_id
   * @return \Drupal\Core\Entity\EntityInterface|false
   */
  public function getComplementBySRHId($srh_id, $langCode);


  /**
   * @param $srh_id
   * @return mixed
   */
  public function getSRHComplement($srh_id);

  /**
   * @param $locale
   *  The srh locale code
   * @return int
   */
  public function getLocaleSync($locale);

  /**
   * @param $locale
   *  The srh locale code
   * @return mixed
   */
  public function updateLocaleSync($locale);

  /**
   * @param NodeInterface $recipe
   * @return mixed
   */
  public function reSyncComplement(NodeInterface $complement);

}

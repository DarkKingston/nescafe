<?php

namespace Drupal\dsu_c_core\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Classy group entities.
 */
interface ClassyGroupInterface extends ConfigEntityInterface {

  /**
   * Returns a boolean indicating if group is multiple or not.
   *
   * @return boolean
   *   A boolean indicating if group is multiple or not.
   */
  public function isMultiple();

  /**
   * Set a boolean indicating if group is multiple or not.
   *
   * @param $multiple boolean
   *   A boolean indicating if group is multiple or not.
   */
  public function setMultiple($multiple);

  /**
   * Returns paragraph bundles list.
   *
   * @return array
   *   Paragraph bundles references.
   */
  public function getBundles();

  /**
   * Set paragraph bundles list.
   *
   * @param $bundles array
   *   Paragraph bundles references.
   */
  public function setBundles($bundles);


  /**
   * Returns classy list.
   *
   * @return array
   *   Classy references.
   */
  public function getClassys();

  /**
   * Set classy list.
   *
   * @param $classys array
   *   Classy references.
   */
  public function setClassys($classys);

}

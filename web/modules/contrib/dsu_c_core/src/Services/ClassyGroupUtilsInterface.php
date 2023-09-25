<?php

namespace Drupal\dsu_c_core\Services;

/**
 * Interface ClassyGroupUtilsInterface.
 */
interface ClassyGroupUtilsInterface {

  /**
   * Return classy groups for a paragraph bundle
   * @param string $bundle
   * @param boolean $include_all
   * @return array
   */
  public function getClassyGroupsForPagragraphBundle($bundle, $include_all = TRUE);

  /**
   * Return classy list for use in paragraph bundle
   * @param string $bundle
   * @param boolean $include_all
   * @return array
   */
  public function getClassysForPagragraphBundle($bundle, $include_all = TRUE);
}

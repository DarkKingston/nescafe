<?php

namespace Drupal\dsu_c_core\Services;

use Drupal\classy_paragraphs\Entity\ClassyParagraphsStyle;
use Drupal\dsu_c_core\CCoreConstants;
use Drupal\dsu_c_core\Entity\ClassyGroup;

/**
 * Class ClassyGroupUtils
 */
class ClassyGroupUtils implements ClassyGroupUtilsInterface {

  /**
   * @inheritdoc
   */
  public function getClassyGroupsForPagragraphBundle($bundle, $include_all = TRUE) {
    $groups = ClassyGroup::loadMultiple();
    $bundle_groups = [];
    foreach ($groups as $group){
      foreach($group->getBundles() as $group_bundle){
        if($group_bundle == $bundle || ($include_all && ($group_bundle == CCoreConstants::PARAGRAPH_GROUP_ALL_BUNDLES))){
          $bundle_groups[] = $group;
        }
      }
    }

    return $bundle_groups;
  }

  /**
   * @inheritdoc
   */
  public function getClassysForPagragraphBundle($bundle, $include_all = TRUE) {
    $classys = [];
    foreach ($this->getClassyGroupsForPagragraphBundle($bundle, $include_all) as $group){
      $classys = [...$classys, ...$group->getClassys()];
    }

    return $classys;
  }
}

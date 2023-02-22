<?php

namespace Drupal\dsu_c_core\Services;


use Drupal\Core\Entity\EntityInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Class CCoreUtils
 */
class CCoreUtils implements CCoreUtilsInterface {

  /**
   * @inheritdoc
   */
  public function entitiesToOptions($entities) {
    $options = [];
    foreach ($entities as $entity) {
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      $options[$entity->id()] = $entity->label();
    }

    return $options;
  }

  /**
   * @inheritdoc
   */
  public function getParagraphRealParentEntity(EntityInterface $entity) {
    if($entity instanceof ParagraphInterface){
      if($parent = $entity->getParentEntity()){
        $entity = $this->getParagraphRealParentEntity($parent);
      }
    }

    return $entity;
  }
}

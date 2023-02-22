<?php

namespace Drupal\dsu_c_core\Services;

use Drupal\Core\Entity\EntityInterface;

/**
 * Interface BaseUtilsInterface.
 */
interface CCoreUtilsInterface {

  /**
   * Prepare options form from entities list
   * @param array $entities
   * @return array
   */
  public function entitiesToOptions($entities);

  /**
   * Return the real parent entity in nested paragraphs
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @return \Drupal\Core\Entity\EntityInterface
   */
  public function getParagraphRealParentEntity(EntityInterface $entity);
}

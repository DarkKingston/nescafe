<?php

namespace Drupal\dsu_c_core;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Classy group entities.
 */
class ClassyParagraphsGroupListBuilder extends ConfigEntityListBuilder {
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Classy group');
    $header['id'] = $this->t('Machine name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    return $row + parent::buildRow($entity);
  }

}

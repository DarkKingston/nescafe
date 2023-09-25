<?php

namespace Drupal\ln_campaign;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of campaign type entities.
 *
 * @see \Drupal\ln_campaign\Entity\LnCampaignType
 */
class LnCampaignTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['title'] = $this->t('Label');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['title'] = [
      'data' => $entity->label(),
      'class' => ['menu-label'],
    ];

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();

    $build['table']['#empty'] = $this->t(
      'No campaign types available. <a href=":link">Add campaign type</a>.',
      [':link' => Url::fromRoute('entity.ln_campaign_type.add_form')->toString()]
    );

    return $build;
  }

}

<?php

namespace Drupal\ln_notification\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Defines the list builder.
 */
class FCMSubscriptionListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    return [
      'id' => $this->t('ID'),
      'belongs' => $this->t('Belongs to'),
      'created' => $this->t('Created at'),
      'changed' => $this->t('Updated at'),
      'timezone' => $this->t('User timezone'),
      'browser' => $this->t('User agent'),
      'token' => $this->t('Token'),
    ];
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function buildRow(EntityInterface $entity): array {
    \assert($entity instanceof FCMSubscriptionInterface);
    return [
      'id' => $entity->id(),
      'belongs' => $entity->getOwner()->toLink(),
      'created' => $entity->getCreatedDate()->format('Y-m-d, H:i:s'),
      'changed' => $entity->getChangedDate()->format('Y-m-d, H:i:s'),
      'timezone' => $entity->getUserTimeZone()->getName(),
      'browser' => $entity->getUserAgent(),
      'token' => $entity->getToken(),
    ];
  }

}

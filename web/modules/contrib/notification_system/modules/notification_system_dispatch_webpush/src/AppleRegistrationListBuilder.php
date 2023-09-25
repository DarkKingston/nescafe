<?php

namespace Drupal\notification_system_dispatch_webpush;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;
use Drupal\notification_system_dispatch_webpush\Entity\AppleRegistrationInterface;

/**
 * Defines a class to build a listing of Apple registration entities.
 *
 * @ingroup notification_system_dispatch_webpush
 */
class AppleRegistrationListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['uid'] = $this->t('User');
    $header['device_token'] = $this->t('Device Token');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    if ($entity instanceof AppleRegistrationInterface) {
      $row['id'] = $entity->id();
      $row['uid'] = $entity->getUser()->label();
      $row['device_token'] = $entity->getDeviceToken();
      return $row + parent::buildRow($entity);
    }

    return parent::buildRow($entity);
  }

}
